<?php
require_once __DIR__ . '/../bootstrap.php';

$db->beginTransaction();

try {
    $divModel = new Dividend($db);
    // Find an asset
    $asset = (new Asset($db))->all()[0];
    $wallet = (new Wallet($db))->all()[0];

    $divId1 = $divModel->create([
        'ativo_id' => $asset['id'],
        'carteira_id' => $wallet['id'],
        'tipo' => 'DIVIDENDO',
        'valor' => 1500.50,
        'data_pagamento' => '2026-04-15',
        'observacao' => 'Teste dividendo abr/2026'
    ]);

    $divId2 = $divModel->create([
        'ativo_id' => $asset['id'],
        'carteira_id' => $wallet['id'],
        'tipo' => 'JCP',
        'valor' => 850.00,
        'data_pagamento' => '2026-08-20',
        'observacao' => 'Teste jcp ago/2026'
    ]);

    $ps = new PortfolioService($db);
    $renda = $ps->relatorioRendaPassiva();
    echo "Renda Passiva com proventos teste:\n";
    echo "Total ano 2026: R$ " . number_format($renda['total_ano_atual'], 2) . "\n";
    echo "Mês maior arrecadação: " . $renda['maior_mes_nome'] . " (R$ " . number_format($renda['maior_mes_valor'], 2) . ")\n";
    echo "Maior ativo: " . $renda['maior_ativo']['ticker'] . " (R$ " . number_format($renda['maior_ativo']['total'], 2) . ")\n";

    $irpf = $ps->relatorioFiscalIRPF(2026);
    echo "IRPF 2026:\n";
    echo "Total Isentos: R$ " . number_format($irpf['total_isentos'], 2) . "\n";
    echo "Total Exclusivos: R$ " . number_format($irpf['total_exclusivos'], 2) . "\n";

    $db->rollBack();
    echo "Rollback executado com sucesso (banco limpo).\n";
} catch (Exception $e) {
    $db->rollBack();
    echo "Erro: " . $e->getMessage() . "\n";
}
