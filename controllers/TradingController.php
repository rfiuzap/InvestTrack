<?php
declare(strict_types=1);

class TradingController
{
    public static function index(PDO $db): void
    {
        $carteiraSelecionada = resolve_carteira_id();
        $wallets = (new Wallet($db))->all();

        $portfolioService = new PortfolioService($db);
        $posicoes = $portfolioService->calcularCarteira($carteiraSelecionada);
        $posicoesComQtd = array_filter($posicoes, static fn (array $posicao): bool => $posicao['quantidade'] > 0);

        if (!empty($posicoesComQtd)) {
            $ativos = array_values(array_map(static fn (array $p): array => $p['ativo'], $posicoesComQtd));
        } else {
            $ativos = (new Asset($db))->all();
        }

        $tickerSolicitado = strtoupper(trim((string)($_GET['ticker'] ?? '')));
        $ativoSelecionado = null;

        foreach ($ativos as $ativo) {
            if ($ativo['ticker'] === $tickerSolicitado) {
                $ativoSelecionado = $ativo;
                break;
            }
        }

        if ($ativoSelecionado === null && !empty($ativos)) {
            $ativoSelecionado = $ativos[0];
        }

        $pageTitle = 'Trading';
        require BASE_PATH . '/views/trading.php';
    }

    public static function cards(PDO $db): void
    {
        $carteiraSelecionada = resolve_carteira_id();
        $wallets = (new Wallet($db))->all();

        $portfolioService = new PortfolioService($db);
        $posicoes = $portfolioService->calcularCarteira($carteiraSelecionada);

        $ativos = array_values(array_map(
            static function (array $posicao): array {
                $ativo = $posicao['ativo'];
                $ativo['tendencia'] = $posicao['cotacao_atual'] >= $posicao['preco_medio'] ? 'up' : 'down';
                $ativo['quantidade'] = $posicao['quantidade'];
                $ativo['preco_medio'] = $posicao['preco_medio'];
                $ativo['cotacao_atual'] = $posicao['cotacao_atual'];
                $ativo['valor_atual'] = $posicao['valor_atual'];
                $ativo['lucro_nao_realizado'] = $posicao['lucro_nao_realizado'];
                $ativo['variacao_percentual'] = $posicao['variacao_percentual'];
                return $ativo;
            },
            array_filter($posicoes, static fn (array $posicao): bool => $posicao['quantidade'] > 0)
        ));

        $pageTitle = 'Trading · Painel de Mercado B3';
        require BASE_PATH . '/views/trading/cards.php';
    }
}