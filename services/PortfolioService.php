<?php
declare(strict_types=1);

/**
 * Motor de cálculo financeiro da carteira: preço médio, lucro realizado/não realizado,
 * alocação por ativo e evolução patrimonial mensal.
 */
class PortfolioService
{
    private PDO $db;
    private Asset $assetModel;
    private Transaction $transactionModel;
    private Dividend $dividendModel;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->assetModel = new Asset($db);
        $this->transactionModel = new Transaction($db);
        $this->dividendModel = new Dividend($db);
    }

    public function processarPosicao(array $ativo, array $transacoes, array $proventos): array
    {
        $quantidade = 0;
        $precoMedio = 0.0;
        $lucroRealizado = 0.0;

        foreach ($transacoes as $t) {
            $qtd = (int)$t['quantidade'];
            $preco = (float)$t['preco_unitario'];
            $taxas = (float)$t['taxas'];

            if ($t['tipo'] === 'COMPRA') {
                $custoAtual = $quantidade * $precoMedio;
                $custoNovo = ($qtd * $preco) + $taxas;
                $quantidade += $qtd;
                $precoMedio = $quantidade > 0 ? ($custoAtual + $custoNovo) / $quantidade : 0.0;
            } else {
                $lucroRealizado += ($preco - $precoMedio) * $qtd - $taxas;
                $quantidade -= $qtd;
                if ($quantidade <= 0) {
                    $quantidade = 0;
                    $precoMedio = 0.0;
                }
            }
        }

        $cotacaoAtual = (float)($ativo['cotacao_atual'] ?? 0);
        $valorInvestido = $quantidade * $precoMedio;
        $valorAtual = $quantidade * $cotacaoAtual;
        $lucroNaoRealizado = $valorAtual - $valorInvestido;
        $variacaoPercentual = $valorInvestido > 0 ? ($lucroNaoRealizado / $valorInvestido) * 100 : 0.0;

        $totalProventos = array_sum(array_map(static fn($p) => (float)$p['valor'], $proventos));
        $yoc = $valorInvestido > 0 ? ($totalProventos / $valorInvestido) * 100 : 0.0;
        $totalReturn = $lucroNaoRealizado + $lucroRealizado + $totalProventos;

        return [
            'ativo' => $ativo,
            'quantidade' => $quantidade,
            'preco_medio' => $precoMedio,
            'cotacao_atual' => $cotacaoAtual,
            'valor_investido' => $valorInvestido,
            'valor_atual' => $valorAtual,
            'lucro_nao_realizado' => $lucroNaoRealizado,
            'variacao_percentual' => $variacaoPercentual,
            'lucro_realizado' => $lucroRealizado,
            'total_proventos' => $totalProventos,
            'yoc' => $yoc,
            'total_return' => $totalReturn,
        ];
    }

    public function calcularPosicaoAtivo(array $ativo, ?int $carteiraId = null, ?int $excludeTransactionId = null): array
    {
        $transacoes = $this->transactionModel->allByAsset((int)$ativo['id'], $carteiraId, $excludeTransactionId);
        $proventos = $this->dividendModel->allByAsset((int)$ativo['id'], $carteiraId);
        return $this->processarPosicao($ativo, $transacoes, $proventos);
    }

    /**
     * Calcula todas as posições da carteira em lote (Batch Hydration),
     * eliminando o problema N+1 queries. Reduz de 120+ queries para apenas 3 queries SQL.
     */
    public function calcularCarteira(?int $carteiraId = null): array
    {
        $ativos = $this->assetModel->all();
        $transacoes = $this->transactionModel->allChronological($carteiraId);
        $proventos = $this->dividendModel->allChronological($carteiraId);

        // Agrupar transações por ativo_id em memória
        $transacoesPorAtivo = [];
        foreach ($transacoes as $t) {
            $transacoesPorAtivo[(int)$t['ativo_id']][] = $t;
        }

        // Agrupar proventos por ativo_id em memória
        $proventosPorAtivo = [];
        foreach ($proventos as $p) {
            $proventosPorAtivo[(int)$p['ativo_id']][] = $p;
        }

        $posicoes = [];
        foreach ($ativos as $ativo) {
            $ativoId = (int)$ativo['id'];
            $posicoes[] = $this->processarPosicao(
                $ativo,
                $transacoesPorAtivo[$ativoId] ?? [],
                $proventosPorAtivo[$ativoId] ?? []
            );
        }

        return $posicoes;
    }

    public function resumoGeral(?int $carteiraId = null): array
    {
        $posicoes = $this->calcularCarteira($carteiraId);

        $valorInvestidoTotal = 0.0;
        $valorAtualTotal = 0.0;
        $lucroRealizadoTotal = 0.0;
        $totalProventos = 0.0;
        $qtdAtivosAtivos = 0;
        $maiorAtivo = null;
        $maiorValorAtivo = 0.0;

        foreach ($posicoes as $p) {
            if ($p['quantidade'] > 0) {
                $valorInvestidoTotal += $p['valor_investido'];
                $valorAtualTotal += $p['valor_atual'];
                $qtdAtivosAtivos++;

                if ($p['valor_atual'] > $maiorValorAtivo) {
                    $maiorValorAtivo = $p['valor_atual'];
                    $maiorAtivo = $p['ativo']['ticker'];
                }
            }
            $lucroRealizadoTotal += $p['lucro_realizado'];
            $totalProventos += $p['total_proventos'];
        }

        $lucroNaoRealizadoTotal = $valorAtualTotal - $valorInvestidoTotal;
        $variacaoPercentualTotal = $valorInvestidoTotal > 0
            ? ($lucroNaoRealizadoTotal / $valorInvestidoTotal) * 100
            : 0.0;

        // Novos KPIs de Renda & Performance
        $totalReturnTotal = $lucroNaoRealizadoTotal + $lucroRealizadoTotal + $totalProventos;
        $totalReturnPercentual = $valorInvestidoTotal > 0
            ? ($totalReturnTotal / $valorInvestidoTotal) * 100
            : 0.0;

        $yocMedio = $valorInvestidoTotal > 0
            ? ($totalProventos / $valorInvestidoTotal) * 100
            : 0.0;

        $concentracaoMaximaPercent = $valorAtualTotal > 0
            ? ($maiorValorAtivo / $valorAtualTotal) * 100
            : 0.0;

        $mediaMensalProventos = $this->calcularMediaMensalProventos($carteiraId);

        return [
            'posicoes' => $posicoes,
            'valor_investido_total' => $valorInvestidoTotal,
            'valor_atual_total' => $valorAtualTotal,
            'lucro_nao_realizado_total' => $lucroNaoRealizadoTotal,
            'variacao_percentual_total' => $variacaoPercentualTotal,
            'lucro_realizado_total' => $lucroRealizadoTotal,
            'total_proventos' => $totalProventos,
            'qtd_ativos_ativos' => $qtdAtivosAtivos,
            'total_return_total' => $totalReturnTotal,
            'total_return_percentual' => $totalReturnPercentual,
            'yoc_medio' => $yocMedio,
            'maior_ativo' => $maiorAtivo,
            'concentracao_maxima_percent' => $concentracaoMaximaPercent,
            'media_mensal_proventos' => $mediaMensalProventos,
        ];
    }

    public function calcularMediaMensalProventos(?int $carteiraId = null): float
    {
        $dozeMesesAtras = date('Y-m-01', strtotime('-11 months'));
        $sql = "SELECT SUM(valor) AS total FROM proventos WHERE data_pagamento >= :data_inicio";
        $params = ['data_inicio' => $dozeMesesAtras];
        if ($carteiraId !== null) {
            $sql .= ' AND carteira_id = :carteira_id';
            $params['carteira_id'] = $carteiraId;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        $total12m = (float)($row['total'] ?? 0);
        return $total12m / 12.0;
    }

    public function alocacaoPorAtivo(?int $carteiraId = null, ?array $posicoesPrecalculadas = null, float $thresholdPercent = 3.0): array
    {
        $posicoes = $posicoesPrecalculadas !== null
            ? array_filter($posicoesPrecalculadas, static fn($p) => $p['quantidade'] > 0)
            : array_filter($this->calcularCarteira($carteiraId), static fn($p) => $p['quantidade'] > 0);

        // Ordenar do maior para o menor valor
        usort($posicoes, static fn($a, $b) => $b['valor_atual'] <=> $a['valor_atual']);

        $totalValor = array_sum(array_map(static fn($p) => $p['valor_atual'], $posicoes));

        $labels = [];
        $valores = [];
        $outrosValor = 0.0;

        foreach ($posicoes as $p) {
            $pct = $totalValor > 0 ? ($p['valor_atual'] / $totalValor) * 100 : 0.0;
            // Se houver mais de 7 ativos e a fatia for menor que o limite, agrupa em 'Outros'
            if (count($posicoes) > 7 && $pct < $thresholdPercent) {
                $outrosValor += $p['valor_atual'];
            } else {
                $labels[] = $p['ativo']['ticker'];
                $valores[] = round($p['valor_atual'], 2);
            }
        }

        if ($outrosValor > 0) {
            $labels[] = 'Outros';
            $valores[] = round($outrosValor, 2);
        }

        $percentuais = [];
        foreach ($valores as $v) {
            $percentuais[] = $totalValor > 0 ? round(($v / $totalValor) * 100, 1) : 0.0;
        }

        return ['labels' => $labels, 'valores' => $valores, 'percentuais' => $percentuais];
    }

    public function evolucaoMensal(?int $carteiraId = null): array
    {
        $sql = "SELECT strftime('%Y-%m', data_operacao) AS mes,
                       SUM(CASE WHEN tipo = 'COMPRA' THEN (quantidade * preco_unitario + taxas)
                                ELSE -(quantidade * preco_unitario - taxas) END) AS delta
                FROM transacoes";
        $sqlProventos = "SELECT strftime('%Y-%m', data_pagamento) AS mes, SUM(valor) AS total FROM proventos";
        $params = [];
        if ($carteiraId !== null) {
            $sql .= ' WHERE carteira_id = :carteira_id';
            $sqlProventos .= ' WHERE carteira_id = :carteira_id';
            $params['carteira_id'] = $carteiraId;
        }
        $sql .= ' GROUP BY mes ORDER BY mes ASC';
        $sqlProventos .= ' GROUP BY mes ORDER BY mes ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        $stmtProventos = $this->db->prepare($sqlProventos);
        $stmtProventos->execute($params);
        $proventosPorMes = [];
        foreach ($stmtProventos->fetchAll() as $r) {
            $proventosPorMes[$r['mes']] = (float)$r['total'];
        }

        $labels = [];
        $investidoAcumulado = [];
        $proventosAcumulado = [];
        $acumulado = 0.0;
        $acumuladoProventos = 0.0;

        foreach ($rows as $r) {
            $acumulado += (float)$r['delta'];
            $acumuladoProventos += $proventosPorMes[$r['mes']] ?? 0.0;
            $labels[] = $r['mes'];
            $investidoAcumulado[] = round($acumulado, 2);
            $proventosAcumulado[] = round($acumuladoProventos, 2);
        }

        return [
            'labels' => $labels,
            'investido_acumulado' => $investidoAcumulado,
            'proventos_acumulado' => $proventosAcumulado,
        ];
    }

    public function relatorioRendaPassiva(?int $carteiraId = null, ?int $anoFiltro = null): array
    {
        $proventos = $this->dividendModel->all($carteiraId);

        $anos = [];
        foreach ($proventos as $p) {
            $ano = (int)date('Y', strtotime($p['data_pagamento']));
            if (!in_array($ano, $anos, true)) {
                $anos[] = $ano;
            }
        }
        rsort($anos);
        $anoAtual = (int)date('Y');
        if (empty($anos)) {
            $anos = [$anoAtual, $anoAtual - 1];
        } elseif (!in_array($anoAtual, $anos, true)) {
            array_unshift($anos, $anoAtual);
        }

        $anoAtivo = $anoFiltro && in_array($anoFiltro, $anos, true) ? $anoFiltro : $anos[0];
        $anoAnterior = $anoAtivo - 1;

        $mesesNomes = [
            1 => 'Jan', 2 => 'Fev', 3 => 'Mar', 4 => 'Abr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago',
            9 => 'Set', 10 => 'Out', 11 => 'Nov', 12 => 'Dez'
        ];

        $totaisAnoAtual = array_fill(1, 12, 0.0);
        $totaisAnoAnterior = array_fill(1, 12, 0.0);
        $matrizAtivos = [];
        $totalAnoAtual = 0.0;
        $totalAnoAnterior = 0.0;

        foreach ($proventos as $p) {
            $data = strtotime($p['data_pagamento']);
            $ano = (int)date('Y', $data);
            $mes = (int)date('n', $data);
            $valor = (float)$p['valor'];
            $ticker = $p['ticker'];

            if ($ano === $anoAtivo) {
                $totaisAnoAtual[$mes] += $valor;
                $totalAnoAtual += $valor;

                if (!isset($matrizAtivos[$ticker])) {
                    $matrizAtivos[$ticker] = [
                        'ticker' => $ticker,
                        'nome' => $p['nome'],
                        'tipo' => $p['ativo_tipo'],
                        'meses' => array_fill(1, 12, 0.0),
                        'total' => 0.0,
                    ];
                }
                $matrizAtivos[$ticker]['meses'][$mes] += $valor;
                $matrizAtivos[$ticker]['total'] += $valor;
            } elseif ($ano === $anoAnterior) {
                $totaisAnoAnterior[$mes] += $valor;
                $totalAnoAnterior += $valor;
            }
        }

        uasort($matrizAtivos, static fn($a, $b) => $b['total'] <=> $a['total']);

        $maiorMesNum = 1;
        $maiorMesValor = 0.0;
        foreach ($totaisAnoAtual as $m => $v) {
            if ($v > $maiorMesValor) {
                $maiorMesValor = $v;
                $maiorMesNum = $m;
            }
        }
        $maiorMesNome = $maiorMesValor > 0 ? $mesesNomes[$maiorMesNum] : '-';

        $maiorAtivoPayer = !empty($matrizAtivos) ? reset($matrizAtivos) : null;
        $mediaMensalAno = $totalAnoAtual / 12.0;

        return [
            'anos' => $anos,
            'ano_ativo' => $anoAtivo,
            'ano_anterior' => $anoAnterior,
            'meses_nomes' => $mesesNomes,
            'totais_ano_atual' => array_values($totaisAnoAtual),
            'totais_ano_anterior' => array_values($totaisAnoAnterior),
            'total_ano_atual' => $totalAnoAtual,
            'total_ano_anterior' => $totalAnoAnterior,
            'media_mensal_ano' => $mediaMensalAno,
            'maior_mes_nome' => $maiorMesNome,
            'maior_mes_valor' => $maiorMesValor,
            'maior_ativo' => $maiorAtivoPayer,
            'matriz_ativos' => array_values($matrizAtivos),
        ];
    }

    public function relatorioFiscalIRPF(int $anoCalendario, ?int $carteiraId = null): array
    {
        $ativos = $this->assetModel->all();
        $transacoes = $this->transactionModel->allChronological($carteiraId);
        $proventos = $this->dividendModel->all($carteiraId);

        $fimAnoAnterior = sprintf('%04d-12-31 23:59:59', $anoCalendario - 1);
        $fimAnoAtual = sprintf('%04d-12-31 23:59:59', $anoCalendario);
        $inicioAnoAtual = sprintf('%04d-01-01', $anoCalendario);
        $fimAnoAtualData = sprintf('%04d-12-31', $anoCalendario);

        $txPorAtivoAnterior = [];
        $txPorAtivoAtual = [];

        foreach ($transacoes as $t) {
            $ativoId = (int)$t['ativo_id'];
            $dataOp = $t['data_operacao'];
            if ($dataOp <= $fimAnoAnterior) {
                $txPorAtivoAnterior[$ativoId][] = $t;
            }
            if ($dataOp <= $fimAnoAtual) {
                $txPorAtivoAtual[$ativoId][] = $t;
            }
        }

        $bensEDireitos = [];
        $totalBensAnterior = 0.0;
        $totalBensAtual = 0.0;

        foreach ($ativos as $ativo) {
            $ativoId = (int)$ativo['id'];
            $posAnterior = $this->processarPosicao($ativo, $txPorAtivoAnterior[$ativoId] ?? [], []);
            $posAtual = $this->processarPosicao($ativo, $txPorAtivoAtual[$ativoId] ?? [], []);

            $qtdAnt = $posAnterior['quantidade'];
            $pmAnt = $posAnterior['preco_medio'];
            $valorAnt = $qtdAnt * $pmAnt;

            $qtdAtu = $posAtual['quantidade'];
            $pmAtu = $posAtual['preco_medio'];
            $valorAtu = $qtdAtu * $pmAtu;

            if ($qtdAnt > 0 || $qtdAtu > 0) {
                $tipo = $ativo['tipo'];
                $grupo = '03 - Participações societárias';
                $codigo = '01 - Ações (inclusive as listadas em bolsa)';
                if ($tipo === 'FII') {
                    $grupo = '07 - Fundos';
                    $codigo = '03 - Fundos de Investimento Imobiliário (FII)';
                } elseif ($tipo === 'ETF') {
                    $grupo = '07 - Fundos';
                    $codigo = '09 - Demais fundos de índice de mercado (ETF)';
                } elseif ($tipo === 'BDR') {
                    $grupo = '04 - Aplicações e Investimentos';
                    $codigo = '04 - Ativos negociados no exterior (BDRs)';
                }

                $discriminacao = sprintf(
                    '%d cotas/ações de %s (%s). Custo médio de aquisição R$ %s.',
                    $qtdAtu,
                    $ativo['ticker'],
                    $ativo['nome'],
                    number_format($pmAtu, 2, ',', '.')
                );

                $bensEDireitos[] = [
                    'ativo' => $ativo,
                    'grupo' => $grupo,
                    'codigo' => $codigo,
                    'discriminacao' => $discriminacao,
                    'quantidade_anterior' => $qtdAnt,
                    'situacao_anterior' => $valorAnt,
                    'quantidade_atual' => $qtdAtu,
                    'situacao_atual' => $valorAtu,
                ];

                $totalBensAnterior += $valorAnt;
                $totalBensAtual += $valorAtu;
            }
        }

        $rendimentosIsentos = [];
        $rendimentosExclusivos = [];
        $totalIsentos = 0.0;
        $totalExclusivos = 0.0;

        foreach ($proventos as $p) {
            $dp = $p['data_pagamento'];
            if ($dp >= $inicioAnoAtual && $dp <= $fimAnoAtualData) {
                $ticker = $p['ticker'];
                $nome = $p['nome'];
                $tipoProv = $p['tipo'];
                $tipoAtivo = $p['ativo_tipo'];
                $valor = (float)$p['valor'];

                if ($tipoProv === 'JCP') {
                    if (!isset($rendimentosExclusivos[$ticker])) {
                        $rendimentosExclusivos[$ticker] = [
                            'ticker' => $ticker,
                            'nome' => $nome,
                            'codigo' => '10 - Juros sobre capital próprio',
                            'tipo' => 'JCP',
                            'valor' => 0.0,
                        ];
                    }
                    $rendimentosExclusivos[$ticker]['valor'] += $valor;
                    $totalExclusivos += $valor;
                } else {
                    $codigo = ($tipoAtivo === 'FII' || $tipoProv === 'RENDIMENTO')
                        ? '26 - Outros (Rendimentos de FIIs isentos)'
                        : '09 - Lucros e dividendos recebidos';

                    if (!isset($rendimentosIsentos[$ticker])) {
                        $rendimentosIsentos[$ticker] = [
                            'ticker' => $ticker,
                            'nome' => $nome,
                            'codigo' => $codigo,
                            'tipo' => $tipoProv,
                            'valor' => 0.0,
                        ];
                    }
                    $rendimentosIsentos[$ticker]['valor'] += $valor;
                    $totalIsentos += $valor;
                }
            }
        }

        return [
            'ano_calendario' => $anoCalendario,
            'bens_e_direitos' => $bensEDireitos,
            'total_bens_anterior' => $totalBensAnterior,
            'total_bens_atual' => $totalBensAtual,
            'rendimentos_isentos' => array_values($rendimentosIsentos),
            'total_isentos' => $totalIsentos,
            'rendimentos_exclusivos' => array_values($rendimentosExclusivos),
            'total_exclusivos' => $totalExclusivos,
        ];
    }
}

