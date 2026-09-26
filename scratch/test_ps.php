<?php
require_once __DIR__ . '/../bootstrap.php';

$ps = new PortfolioService($db);
$resumo = $ps->resumoGeral();
echo "Resumo Geral OK! Posicoes: " . count($resumo['posicoes']) . "\n";
echo "Total Investido: R$ " . number_format($resumo['valor_investido_total'], 2) . "\n";
echo "Total Return: R$ " . number_format($resumo['total_return_total'], 2) . "\n";
echo "YoC Médio: " . number_format($resumo['yoc_medio'], 2) . "%\n";

$renda = $ps->relatorioRendaPassiva();
echo "Renda Passiva OK! Anos: " . implode(', ', $renda['anos']) . "\n";

$irpf = $ps->relatorioFiscalIRPF(2025);
echo "IRPF 2025 OK! Bens e Direitos: " . count($irpf['bens_e_direitos']) . "\n";
echo "Total Bens 2025: R$ " . number_format($irpf['total_bens_atual'], 2) . "\n";

$irpf2026 = $ps->relatorioFiscalIRPF(2026);
echo "IRPF 2026 OK! Bens e Direitos: " . count($irpf2026['bens_e_direitos']) . "\n";
echo "Total Bens 2026: R$ " . number_format($irpf2026['total_bens_atual'], 2) . "\n";
