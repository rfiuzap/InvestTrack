<?php
declare(strict_types=1);

/**
 * Busca cotações de ativos B3 via API pública da própria B3 (cotacao.b3.com.br), com
 * dicionário local e brapi.dev como fallbacks, e entrada manual em caso de falha total.
 */
class QuoteService
{
    private const API_URL = 'https://brapi.dev/api/quote/';
    private const B3_QUOTE_URL = 'https://cotacao.b3.com.br/mds/api/v1/instrumentQuotation/';

    private function buscarResultado(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));
        $url = self::API_URL . rawurlencode($ticker);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        return $data['results'][0] ?? null;
    }

    /**
     * Consulta a API pública oficial da B3 (mesma fonte usada pelo site cotacao.b3.com.br),
     * que não exige token e cobre praticamente qualquer ticker ativo na bolsa.
     */
    private function buscarResultadoB3(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));
        $url = self::B3_QUOTE_URL . rawurlencode($ticker);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: Mozilla/5.0'],
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error || $httpCode !== 200 || !$response) {
            return null;
        }

        $data = json_decode($response, true);
        return $data['Trad'][0]['scty'] ?? null;
    }

    public function buscarCotacao(string $ticker): ?float
    {
        $b3 = $this->buscarResultadoB3($ticker);
        $preco = $b3['SctyQtn']['curPrc'] ?? null;
        if (is_numeric($preco)) {
            return (float)$preco;
        }

        $preco = $this->buscarResultado($ticker)['regularMarketPrice'] ?? null;
        return is_numeric($preco) ? (float)$preco : null;
    }

    /**
     * Retorna nome e cotação do ativo a partir do ticker, usado para autocompletar o cadastro.
     * Ordem de busca: dicionário local (nomes completos e offline) -> API oficial da B3
     * (cobre qualquer ticker ativo, sem token) -> brapi.dev (fallback best-effort).
     */
    public function buscarInfo(string $ticker): ?array
    {
        $ticker = strtoupper(trim($ticker));
        $dicionario = require BASE_PATH . '/config/b3_tickers.php';
        $nomeLocal = $dicionario[$ticker] ?? null;

        $b3 = $this->buscarResultadoB3($ticker);
        $precoB3 = $b3['SctyQtn']['curPrc'] ?? null;
        $precoB3 = is_numeric($precoB3) ? (float)$precoB3 : null;

        if ($nomeLocal !== null) {
            return ['nome' => $nomeLocal, 'preco' => $precoB3];
        }

        $nomeB3 = trim((string)($b3['desc'] ?? ''));
        if ($nomeB3 !== '') {
            return ['nome' => $nomeB3, 'preco' => $precoB3];
        }

        $resultado = $this->buscarResultado($ticker);
        $nome = $resultado['longName'] ?? $resultado['shortName'] ?? null;
        if ($nome === null) {
            return null;
        }

        $preco = $resultado['regularMarketPrice'] ?? null;
        return [
            'nome' => $nome,
            'preco' => is_numeric($preco) ? (float)$preco : null,
        ];
    }

    public function atualizarCotacaoAtivo(PDO $db, int $ativoId, string $ticker): ?float
    {
        $preco = $this->buscarCotacao($ticker);
        if ($preco === null) {
            return null;
        }
        (new Asset($db))->updateQuote($ativoId, $preco);
        return $preco;
    }

    public function atualizarTodasCotacoes(PDO $db, bool $force = true): array
    {
        $assetModel = new Asset($db);
        $ativos = $assetModel->all();
        if (empty($ativos)) {
            return [];
        }

        $tickersToFetch = [];
        $resultado = [];

        foreach ($ativos as $ativo) {
            $ticker = strtoupper(trim($ativo['ticker']));
            if (!$force && !empty($ativo['cotacao_atualizada_em'])) {
                $diff = time() - strtotime($ativo['cotacao_atualizada_em']);
                if ($diff < 900 && (float)$ativo['cotacao_atual'] > 0) {
                    $resultado[$ticker] = (float)$ativo['cotacao_atual'];
                    continue;
                }
            }
            $tickersToFetch[$ticker] = (int)$ativo['id'];
        }

        if (empty($tickersToFetch)) {
            return $resultado;
        }

        // 1. Executar requisições paralelas à API oficial da B3 via curl_multi
        $mh = curl_multi_init();
        $handles = [];

        foreach ($tickersToFetch as $ticker => $id) {
            $ch = curl_init(self::B3_QUOTE_URL . rawurlencode($ticker));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 6,
                CURLOPT_CONNECTTIMEOUT => 3,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HTTPHEADER => ['Accept: application/json', 'User-Agent: Mozilla/5.0'],
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[$ticker] = $ch;
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($mh, $active);
        } while ($mrc === CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc === CURLM_OK) {
            if (curl_multi_select($mh) !== -1) {
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc === CURLM_CALL_MULTI_PERFORM);
            } else {
                usleep(50000);
                do {
                    $mrc = curl_multi_exec($mh, $active);
                } while ($mrc === CURLM_CALL_MULTI_PERFORM);
            }
        }

        $fallbacks = [];

        foreach ($handles as $ticker => $ch) {
            $content = curl_multi_getcontent($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);

            $preco = null;
            if ($httpCode === 200 && $content) {
                $data = json_decode($content, true);
                $p = $data['Trad'][0]['scty']['SctyQtn']['curPrc'] ?? null;
                if (is_numeric($p) && (float)$p > 0) {
                    $preco = (float)$p;
                }
            }

            if ($preco !== null) {
                $assetModel->updateQuote($tickersToFetch[$ticker], $preco);
                $resultado[$ticker] = $preco;
            } else {
                $fallbacks[$ticker] = $tickersToFetch[$ticker];
            }
        }

        curl_multi_close($mh);

        // 2. Para tickers pendentes ou que falharam na B3, tenta fallback individual
        foreach ($fallbacks as $ticker => $id) {
            $resultado[$ticker] = $this->atualizarCotacaoAtivo($db, $id, $ticker);
        }

        return $resultado;
    }
}
