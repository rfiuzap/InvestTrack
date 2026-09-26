<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="space-y-4 mb-6">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <!-- Abas de Carteira -->
    <?php require BASE_PATH . '/views/layout/wallet_tabs.php'; ?>

    <!-- Seletor de Sub-Abas do Relatório -->
    <div class="inline-flex items-center gap-1 rounded-xl border border-slate-200/80 bg-white/75 backdrop-blur-md p-1 shadow-sm" id="relatorio-tab-nav">
      <button type="button" data-tab="desempenho"
        class="tab-nav-btn inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all duration-150 <?= $abaAtiva === 'desempenho' ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_4px_12px_rgba(29,78,216,0.22)]' : 'text-slate-600 hover:bg-white hover:text-slate-900' ?>">
        <i class="ph ph-chart-line-up text-sm"></i> Desempenho Geral
      </button>
      <button type="button" data-tab="renda"
        class="tab-nav-btn inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all duration-150 <?= $abaAtiva === 'renda' ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_4px_12px_rgba(29,78,216,0.22)]' : 'text-slate-600 hover:bg-white hover:text-slate-900' ?>">
        <i class="ph ph-calendar-check text-sm"></i> Renda Passiva & Sazonalidade
      </button>
      <button type="button" data-tab="irpf"
        class="tab-nav-btn inline-flex items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-xs font-semibold transition-all duration-150 <?= $abaAtiva === 'irpf' ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_4px_12px_rgba(29,78,216,0.22)]' : 'text-slate-600 hover:bg-white hover:text-slate-900' ?>">
        <i class="ph ph-receipt text-sm"></i> Apoio ao IRPF
      </button>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ABA 1: DESEMPENHO GERAL -->
<!-- ========================================================================= -->
<div id="tab-content-desempenho" class="tab-pane <?= $abaAtiva === 'desempenho' ? '' : 'hidden' ?> space-y-6">
  <!-- KPIs de Desempenho -->
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="kpi-card">
      <div class="kpi-rotulo">Lucro Realizado</div>
      <?php $lr = $resumo['lucro_realizado_total']; ?>
      <div class="kpi-valor <?= $lr >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
        R$ <?= number_format($lr, 2, ',', '.') ?>
      </div>
      <p class="mt-2 text-xs font-medium text-slate-400">Ganhos e perdas consolidados de vendas</p>
    </div>

    <div class="kpi-card">
      <div class="kpi-rotulo">Lucro Não Realizado</div>
      <?php $lnr = $resumo['lucro_nao_realizado_total']; ?>
      <div class="kpi-valor <?= $lnr >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
        R$ <?= number_format($lnr, 2, ',', '.') ?>
      </div>
      <p class="mt-2 text-xs font-medium text-slate-400">Variação da posição em carteira</p>
    </div>

    <div class="kpi-card">
      <div class="kpi-rotulo">Total de Proventos</div>
      <div class="kpi-valor text-slate-900">R$ <?= number_format($resumo['total_proventos'], 2, ',', '.') ?></div>
      <p class="mt-2 text-xs font-medium text-slate-400">Fluxo acumulado de dividendos e JCP</p>
    </div>

    <div class="kpi-card">
      <div class="kpi-rotulo">Retorno Global (Total Return)</div>
      <?php $tr = $resumo['total_return_total']; $trPct = $resumo['total_return_percentual']; ?>
      <div class="kpi-valor <?= $tr >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
        R$ <?= number_format($tr, 2, ',', '.') ?>
      </div>
      <p class="mt-2 text-xs font-medium text-slate-400">
        <span class="font-bold <?= $tr >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
          <?= ($tr >= 0 ? '+' : '') . number_format($trPct, 2, ',', '.') ?>%
        </span> de retorno total acumulado
      </p>
    </div>
  </section>

  <!-- Tabela Resultado Detalhado por Ativo -->
  <div class="tabela-card">
    <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
          <i class="ph ph-chart-polar text-blue-700 text-base"></i> Resultado Detalhado por Ativo
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">Visão consolidada de lucros, proventos recebidos e Yield on Cost.</p>
      </div>
      <button type="button" onclick="exportarTabelaCSV('tabela-resultado-ativo', 'resultado_ativos_investtrack')"
        class="btn-secondary inline-flex items-center gap-1.5 text-xs font-bold py-1.5 px-3 shadow-sm">
        <i class="ph ph-file-csv text-base text-emerald-600"></i> Exportar CSV
      </button>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm js-sortable" id="tabela-resultado-ativo">
        <thead>
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
            <th class="py-3 px-5" data-sort-type="text">Ativo</th>
            <th class="py-3 px-5">Qtd. Atual</th>
            <th class="py-3 px-5">Lucro Realizado</th>
            <th class="py-3 px-5">Lucro Não Realizado</th>
            <th class="py-3 px-5">Proventos</th>
            <th class="py-3 px-5">Yield on Cost (YoC)</th>
            <th class="py-3 px-5">Total Return</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($resumo['posicoes'])): ?>
            <tr><td colspan="7" class="py-8 text-center text-slate-400">Nenhum ativo cadastrado.</td></tr>
          <?php endif; ?>
          <?php foreach ($resumo['posicoes'] as $p): ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-5 font-bold text-slate-900 tracking-tight" data-sort-value="<?= e($p['ativo']['ticker']) ?>">
              <?= e($p['ativo']['ticker']) ?>
              <span class="text-xs font-normal text-slate-400 ml-1"><?= e($p['ativo']['nome']) ?></span>
            </td>
            <td class="py-3.5 px-5 text-slate-700 font-medium"><?= number_format($p['quantidade'], 0, ',', '.') ?></td>
            <td class="py-3.5 px-5 font-bold <?= $p['lucro_realizado'] >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
              R$ <?= number_format($p['lucro_realizado'], 2, ',', '.') ?>
            </td>
            <td class="py-3.5 px-5 font-bold <?= $p['lucro_nao_realizado'] >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
              R$ <?= number_format($p['lucro_nao_realizado'], 2, ',', '.') ?>
            </td>
            <td class="py-3.5 px-5 text-slate-900 font-medium">R$ <?= number_format($p['total_proventos'], 2, ',', '.') ?></td>
            <td class="py-3.5 px-5 text-slate-700 font-bold">
              <?= $p['yoc'] > 0 ? number_format($p['yoc'], 2, ',', '.') . '%' : '-' ?>
            </td>
            <td class="py-3.5 px-5 font-bold <?= $p['total_return'] >= 0 ? 'text-emerald-600' : 'text-rose-600' ?>">
              R$ <?= number_format($p['total_return'], 2, ',', '.') ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Tabela Histórico de Proventos Recebidos -->
  <div class="tabela-card">
    <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
          <i class="ph ph-coins text-blue-700 text-base"></i> Histórico de Proventos Recebidos
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">Extrato completo de lançamentos de proventos da carteira.</p>
      </div>
      <button type="button" onclick="exportarTabelaCSV('tabela-relatorio-proventos', 'historico_proventos_investtrack')"
        class="btn-secondary inline-flex items-center gap-1.5 text-xs font-bold py-1.5 px-3 shadow-sm">
        <i class="ph ph-file-csv text-base text-emerald-600"></i> Exportar CSV
      </button>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-sm js-sortable" id="tabela-relatorio-proventos">
        <thead>
          <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
            <th class="py-3 px-5" data-default-sort="desc">Data</th>
            <th class="py-3 px-5">Carteira</th>
            <th class="py-3 px-5" data-sort-type="text">Ativo</th>
            <th class="py-3 px-5">Tipo</th>
            <th class="py-3 px-5">Valor</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($proventos)): ?>
            <tr><td colspan="5" class="py-8 text-center text-slate-400">Nenhum provento registrado.</td></tr>
          <?php endif; ?>
          <?php foreach ($proventos as $p): ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3.5 px-5 text-slate-600 font-medium" data-sort-value="<?= e($p['data_pagamento']) ?>"><?= date('d/m/Y', strtotime($p['data_pagamento'])) ?></td>
            <td class="py-3.5 px-5"><?= carteira_badge($p['carteira_nome']) ?></td>
            <td class="py-3.5 px-5 font-bold text-slate-900 tracking-tight" data-sort-value="<?= e($p['ticker']) ?>"><?= e($p['ticker']) ?></td>
            <td class="py-3.5 px-5">
              <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-bold bg-violet-50 text-violet-700 border border-violet-200/80"><?= e($p['tipo']) ?></span>
            </td>
            <td class="py-3.5 px-5 text-emerald-600 font-bold">R$ <?= number_format($p['valor'], 2, ',', '.') ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ABA 2: RENDA PASSIVA & SAZONALIDADE -->
<!-- ========================================================================= -->
<div id="tab-content-renda" class="tab-pane <?= $abaAtiva === 'renda' ? '' : 'hidden' ?> space-y-6">
  <!-- Seletor de Ano da Renda Passiva -->
  <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-white/75 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-sm">
    <div class="flex items-center gap-2">
      <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ano de Análise:</span>
      <div class="inline-flex items-center gap-1 rounded-xl bg-slate-100 p-1">
        <?php foreach ($rendaPassiva['anos'] as $anoOpt): ?>
          <a href="<?= BASE_URL ?>/relatorios.php?tab=renda&ano_renda=<?= $anoOpt ?><?= $carteiraSelecionada ? '&carteira_id=' . $carteiraSelecionada : '' ?>"
             class="px-3 py-1 text-xs font-bold rounded-lg transition-all <?= $anoOpt === $rendaPassiva['ano_ativo'] ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">
            <?= $anoOpt ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <p class="text-xs text-slate-500 font-medium">Comparativo com <?= $rendaPassiva['ano_anterior'] ?> e sazonalidade mês a mês.</p>
  </div>

  <!-- KPIs de Renda no Ano -->
  <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="kpi-card">
      <div class="kpi-rotulo">Total Recebido em <?= $rendaPassiva['ano_ativo'] ?></div>
      <div class="kpi-valor text-emerald-600">
        R$ <?= number_format($rendaPassiva['total_ano_atual'], 2, ',', '.') ?>
      </div>
      <p class="mt-2 text-xs font-medium text-slate-400">
        Ano anterior (<?= $rendaPassiva['ano_anterior'] ?>): R$ <?= number_format($rendaPassiva['total_ano_anterior'], 2, ',', '.') ?>
      </p>
    </div>

    <div class="kpi-card">
      <div class="kpi-rotulo">Média Mensal em <?= $rendaPassiva['ano_ativo'] ?></div>
      <div class="kpi-valor text-slate-900">
        R$ <?= number_format($rendaPassiva['media_mensal_ano'], 2, ',', '.') ?><span class="text-xs font-normal text-slate-400">/mês</span>
      </div>
      <p class="mt-2 text-xs font-medium text-slate-400">Considerando 12 meses do ano</p>
    </div>

    <div class="kpi-card">
      <div class="kpi-rotulo">Mês com Maior Arrecadação</div>
      <div class="kpi-valor text-blue-700">
        <?= $rendaPassiva['maior_mes_nome'] ?>
      </div>
      <p class="mt-2 text-xs font-medium text-slate-400">
        Pico de R$ <?= number_format($rendaPassiva['maior_mes_valor'], 2, ',', '.') ?>
      </p>
    </div>

    <div class="kpi-card">
      <div class="kpi-rotulo">Maior Pagador do Ano</div>
      <div class="kpi-valor text-indigo-700">
        <?= $rendaPassiva['maior_ativo'] ? e($rendaPassiva['maior_ativo']['ticker']) : '-' ?>
      </div>
      <p class="mt-2 text-xs font-medium text-slate-400">
        <?= $rendaPassiva['maior_ativo'] ? 'Total: R$ ' . number_format($rendaPassiva['maior_ativo']['total'], 2, ',', '.') : 'Sem proventos no período' ?>
      </p>
    </div>
  </section>

  <!-- Gráfico Comparativo Chart.js -->
  <div class="tabela-card p-5">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
          <i class="ph ph-chart-bar text-blue-700 text-base"></i> Comparativo Mensal de Proventos
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">Evolução dos rendimentos mês a mês: <?= $rendaPassiva['ano_ativo'] ?> vs <?= $rendaPassiva['ano_anterior'] ?>.</p>
      </div>
    </div>
    <div class="h-72 w-full">
      <canvas id="grafico-renda-comparativa"></canvas>
    </div>
  </div>

  <!-- Matriz de Sazonalidade (Heatmap / Tabela Mês a Mês) -->
  <div class="tabela-card">
    <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
          <i class="ph ph-squares-four text-blue-700 text-base"></i> Matriz de Sazonalidade de Proventos (<?= $rendaPassiva['ano_ativo'] ?>)
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">Distribuição temporal de quando cada ativo paga proventos no decorrer do ano.</p>
      </div>
      <button type="button" onclick="exportarTabelaCSV('tabela-matriz-sazonalidade', 'matriz_sazonalidade_<?= $rendaPassiva['ano_ativo'] ?>')"
        class="btn-secondary inline-flex items-center gap-1.5 text-xs font-bold py-1.5 px-3 shadow-sm">
        <i class="ph ph-file-csv text-base text-emerald-600"></i> Exportar Matriz CSV
      </button>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-left js-sortable" id="tabela-matriz-sazonalidade">
        <thead>
          <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
            <th class="py-3 px-3" data-sort-type="text">Ativo</th>
            <th class="py-3 px-2">Tipo</th>
            <?php foreach ($rendaPassiva['meses_nomes'] as $mNome): ?>
              <th class="py-3 px-2 text-right"><?= $mNome ?></th>
            <?php endforeach; ?>
            <th class="py-3 px-3 text-right bg-slate-100/70 font-bold">Total Ano</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($rendaPassiva['matriz_ativos'])): ?>
            <tr><td colspan="15" class="py-8 text-center text-slate-400">Nenhum provento registrado no ano de <?= $rendaPassiva['ano_ativo'] ?>.</td></tr>
          <?php endif; ?>
          <?php foreach ($rendaPassiva['matriz_ativos'] as $item): ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-2.5 px-3 font-bold text-slate-900" data-sort-value="<?= e($item['ticker']) ?>"><?= e($item['ticker']) ?></td>
            <td class="py-2.5 px-2">
              <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-600">
                <?= e($item['tipo']) ?>
              </span>
            </td>
            <?php foreach ($item['meses'] as $mValor): ?>
              <td class="py-2.5 px-2 text-right font-medium <?= $mValor > 0 ? 'bg-emerald-50/60 text-emerald-700 font-bold' : 'text-slate-300' ?>">
                <?= $mValor > 0 ? number_format($mValor, 2, ',', '.') : '-' ?>
              </td>
            <?php endforeach; ?>
            <td class="py-2.5 px-3 text-right font-bold text-slate-900 bg-slate-100/50">
              R$ <?= number_format($item['total'], 2, ',', '.') ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <?php if (!empty($rendaPassiva['matriz_ativos'])): ?>
        <tfoot>
          <tr class="border-t-2 border-slate-300 bg-slate-50/90 font-bold text-slate-800 text-xs">
            <td class="py-3 px-3 uppercase tracking-wider" colspan="2">Total Mensal</td>
            <?php foreach ($rendaPassiva['totais_ano_atual'] as $tMes): ?>
              <td class="py-3 px-2 text-right text-emerald-700">
                <?= $tMes > 0 ? number_format($tMes, 2, ',', '.') : '-' ?>
              </td>
            <?php endforeach; ?>
            <td class="py-3 px-3 text-right text-slate-900 bg-slate-200/60">
              R$ <?= number_format($rendaPassiva['total_ano_atual'], 2, ',', '.') ?>
            </td>
          </tr>
        </tfoot>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>

<!-- ========================================================================= -->
<!-- ABA 3: APOIO AO IRPF (RECEITA FEDERAL) -->
<!-- ========================================================================= -->
<div id="tab-content-irpf" class="tab-pane <?= $abaAtiva === 'irpf' ? '' : 'hidden' ?> space-y-6">
  <!-- Banner Informativo da Receita Federal -->
  <div class="rounded-2xl border border-blue-200/80 bg-blue-50/70 p-4 backdrop-blur-sm flex items-start gap-3 shadow-sm">
    <div class="w-9 h-9 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
      <i class="ph ph-info text-xl"></i>
    </div>
    <div class="text-xs leading-relaxed text-blue-900">
      <h3 class="font-bold text-sm text-blue-950 mb-0.5">Relatório Fiscal de Apoio ao IRPF</h3>
      <p class="text-blue-800">
        Conforme a Instrução Normativa da Receita Federal, as posições na ficha <strong>Bens e Direitos</strong> devem ser informadas pelo <strong>Custo Total de Aquisição</strong> (Preço Médio &times; Quantidade de cotas em 31/12), e <strong>NÃO</strong> pela cotação de mercado. Os proventos são divididos nas fichas de Rendimentos Isentos e Rendimentos Sujeitos à Tributação Exclusiva.
      </p>
    </div>
  </div>

  <!-- Controles: Ano-Calendário e Exportação -->
  <div class="flex flex-wrap items-center justify-between gap-3 p-4 bg-white/75 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-sm">
    <div class="flex items-center gap-2">
      <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Ano-Calendário (Exercício <?= $relatorioIRPF['ano_calendario'] + 1 ?>):</span>
      <div class="inline-flex items-center gap-1 rounded-xl bg-slate-100 p-1">
        <?php
          $anosIrpfDisponiveis = [(int)date('Y'), (int)date('Y') - 1, (int)date('Y') - 2];
          foreach ($anosIrpfDisponiveis as $anoOpt):
        ?>
          <a href="<?= BASE_URL ?>/relatorios.php?tab=irpf&ano_irpf=<?= $anoOpt ?><?= $carteiraSelecionada ? '&carteira_id=' . $carteiraSelecionada : '' ?>"
             class="px-3 py-1 text-xs font-bold rounded-lg transition-all <?= $anoOpt === $relatorioIRPF['ano_calendario'] ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900' ?>">
            <?= $anoOpt ?>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
    <button type="button" onclick="exportarTabelaCSV('tabela-irpf-bens', 'irpf_bens_e_direitos_<?= $relatorioIRPF['ano_calendario'] ?>')"
      class="btn-primary inline-flex items-center gap-2 text-xs font-bold py-2 px-3.5 shadow-sm">
      <i class="ph ph-file-csv text-base"></i> Exportar Relatório IRPF (CSV)
    </button>
  </div>

  <!-- Ficha 1: Bens e Direitos -->
  <div class="tabela-card">
    <div class="p-4 border-b border-slate-100 flex items-center justify-between">
      <div>
        <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
          <i class="ph ph-buildings text-blue-700 text-base"></i> Ficha Bens e Direitos (em 31/12/<?= $relatorioIRPF['ano_calendario'] ?>)
        </h2>
        <p class="text-xs text-slate-400 mt-0.5">Ações (Grupo 03, Cód. 01), FIIs (Grupo 07, Cód. 03), ETFs (Grupo 07, Cód. 09) e BDRs.</p>
      </div>
      <div class="text-right">
        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Custo Total em 31/12:</span>
        <span class="text-sm font-extrabold text-slate-900 ml-1">R$ <?= number_format($relatorioIRPF['total_bens_atual'], 2, ',', '.') ?></span>
      </div>
    </div>
    <div class="overflow-x-auto">
      <table class="w-full text-xs text-left js-sortable" id="tabela-irpf-bens">
        <thead>
          <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
            <th class="py-3 px-3">Código</th>
            <th class="py-3 px-3" data-sort-type="text">Ticker</th>
            <th class="py-3 px-3">Nome do Ativo</th>
            <th class="py-3 px-3">Qtd em 31/12</th>
            <th class="py-3 px-3 text-right">Situação em 31/12/<?= $relatorioIRPF['ano_calendario'] - 1 ?></th>
            <th class="py-3 px-3 text-right">Situação em 31/12/<?= $relatorioIRPF['ano_calendario'] ?></th>
            <th class="py-3 px-4">Discriminação Sugerida</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <?php if (empty($relatorioIRPF['bens_e_direitos'])): ?>
            <tr><td colspan="7" class="py-8 text-center text-slate-400">Nenhum ativo com custódia no período fiscal.</td></tr>
          <?php endif; ?>
          <?php foreach ($relatorioIRPF['bens_e_direitos'] as $b): ?>
          <tr class="hover:bg-slate-50/80 transition-colors">
            <td class="py-3 px-3 font-semibold text-slate-600"><?= e($b['codigo']) ?></td>
            <td class="py-3 px-3 font-bold text-slate-900" data-sort-value="<?= e($b['ativo']['ticker']) ?>"><?= e($b['ativo']['ticker']) ?></td>
            <td class="py-3 px-3 text-slate-600 font-medium"><?= e($b['ativo']['nome']) ?></td>
            <td class="py-3 px-3 text-slate-800 font-bold"><?= number_format($b['quantidade_atual'], 0, ',', '.') ?></td>
            <td class="py-3 px-3 text-right text-slate-600 font-medium">R$ <?= number_format($b['situacao_anterior'], 2, ',', '.') ?></td>
            <td class="py-3 px-3 text-right font-bold text-slate-900">R$ <?= number_format($b['situacao_atual'], 2, ',', '.') ?></td>
            <td class="py-3 px-4 text-slate-500 font-mono text-[11px] max-w-xs truncate" title="<?= e($b['discriminacao']) ?>">
              <?= e($b['discriminacao']) ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="border-t-2 border-slate-300 bg-slate-50/90 font-bold text-slate-900 text-xs">
            <td class="py-3 px-3 uppercase tracking-wider" colspan="4">Totais Consolidados</td>
            <td class="py-3 px-3 text-right">R$ <?= number_format($relatorioIRPF['total_bens_anterior'], 2, ',', '.') ?></td>
            <td class="py-3 px-3 text-right text-blue-700">R$ <?= number_format($relatorioIRPF['total_bens_atual'], 2, ',', '.') ?></td>
            <td></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Ficha 2: Rendimentos Isentos e Não Tributáveis -->
    <div class="tabela-card">
      <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <div>
          <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <i class="ph ph-seal-check text-emerald-600 text-base"></i> Rendimentos Isentos e Não Tributáveis
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">Dividendos de Ações (Cód. 09) e Rendimentos de FIIs (Cód. 26).</p>
        </div>
        <button type="button" onclick="exportarTabelaCSV('tabela-irpf-isentos', 'irpf_rendimentos_isentos_<?= $relatorioIRPF['ano_calendario'] ?>')"
          class="btn-secondary inline-flex items-center gap-1 text-[11px] font-bold py-1 px-2.5 shadow-sm">
          <i class="ph ph-file-csv text-sm text-emerald-600"></i> CSV
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left js-sortable" id="tabela-irpf-isentos">
          <thead>
            <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
              <th class="py-3 px-3">Código</th>
              <th class="py-3 px-3">Ativo</th>
              <th class="py-3 px-3">Tipo</th>
              <th class="py-3 px-3 text-right">Valor Total Isento</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($relatorioIRPF['rendimentos_isentos'])): ?>
              <tr><td colspan="4" class="py-6 text-center text-slate-400">Nenhum rendimento isento no ano-calendário.</td></tr>
            <?php endif; ?>
            <?php foreach ($relatorioIRPF['rendimentos_isentos'] as $ri): ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-2.5 px-3 font-medium text-slate-600"><?= e($ri['codigo']) ?></td>
              <td class="py-2.5 px-3 font-bold text-slate-900"><?= e($ri['ticker']) ?></td>
              <td class="py-2.5 px-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-emerald-50 text-emerald-700">
                  <?= e($ri['tipo']) ?>
                </span>
              </td>
              <td class="py-2.5 px-3 text-right font-bold text-emerald-600">R$ <?= number_format($ri['valor'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="border-t border-slate-200 bg-slate-50/90 font-bold text-slate-900 text-xs">
              <td class="py-2.5 px-3 uppercase tracking-wider" colspan="3">Total Isentos</td>
              <td class="py-2.5 px-3 text-right text-emerald-600">R$ <?= number_format($relatorioIRPF['total_isentos'], 2, ',', '.') ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Ficha 3: Rendimentos Sujeitos à Tributação Exclusiva/Definitiva -->
    <div class="tabela-card">
      <div class="p-4 border-b border-slate-100 flex items-center justify-between">
        <div>
          <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
            <i class="ph ph-scales text-indigo-600 text-base"></i> Rendimentos de Tributação Exclusiva
          </h2>
          <p class="text-xs text-slate-400 mt-0.5">Juros sobre Capital Próprio - JCP (Cód. 10 - 15% IRRF retido na fonte).</p>
        </div>
        <button type="button" onclick="exportarTabelaCSV('tabela-irpf-exclusivos', 'irpf_rendimentos_exclusivos_<?= $relatorioIRPF['ano_calendario'] ?>')"
          class="btn-secondary inline-flex items-center gap-1 text-[11px] font-bold py-1 px-2.5 shadow-sm">
          <i class="ph ph-file-csv text-sm text-emerald-600"></i> CSV
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-xs text-left js-sortable" id="tabela-irpf-exclusivos">
          <thead>
            <tr class="text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
              <th class="py-3 px-3">Código</th>
              <th class="py-3 px-3">Ativo</th>
              <th class="py-3 px-3">Tipo</th>
              <th class="py-3 px-3 text-right">Valor Líquido</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <?php if (empty($relatorioIRPF['rendimentos_exclusivos'])): ?>
              <tr><td colspan="4" class="py-6 text-center text-slate-400">Nenhum JCP recebido no ano-calendário.</td></tr>
            <?php endif; ?>
            <?php foreach ($relatorioIRPF['rendimentos_exclusivos'] as $re): ?>
            <tr class="hover:bg-slate-50/80 transition-colors">
              <td class="py-2.5 px-3 font-medium text-slate-600"><?= e($re['codigo']) ?></td>
              <td class="py-2.5 px-3 font-bold text-slate-900"><?= e($re['ticker']) ?></td>
              <td class="py-2.5 px-3">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-violet-50 text-violet-700">
                  <?= e($re['tipo']) ?>
                </span>
              </td>
              <td class="py-2.5 px-3 text-right font-bold text-slate-900">R$ <?= number_format($re['valor'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr class="border-t border-slate-200 bg-slate-50/90 font-bold text-slate-900 text-xs">
              <td class="py-2.5 px-3 uppercase tracking-wider" colspan="3">Total Exclusivo</td>
              <td class="py-2.5 px-3 text-right text-slate-900">R$ <?= number_format($relatorioIRPF['total_exclusivos'], 2, ',', '.') ?></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
// Gerenciamento de Sub-Abas do Relatório
(function () {
  const tabBtns = document.querySelectorAll('#relatorio-tab-nav .tab-nav-btn');
  const panes = {
    desempenho: document.getElementById('tab-content-desempenho'),
    renda: document.getElementById('tab-content-renda'),
    irpf: document.getElementById('tab-content-irpf'),
  };

  let graficoComparativo = null;

  function ativarAba(tabKey, updateUrl = true) {
    tabBtns.forEach(btn => {
      const match = btn.dataset.tab === tabKey;
      btn.classList.toggle('bg-gradient-to-r', match);
      btn.classList.toggle('from-blue-700', match);
      btn.classList.toggle('to-blue-600', match);
      btn.classList.toggle('text-white', match);
      btn.classList.toggle('shadow-[0_4px_12px_rgba(29,78,216,0.22)]', match);
      btn.classList.toggle('text-slate-600', !match);
      btn.classList.toggle('hover:bg-white', !match);
      btn.classList.toggle('hover:text-slate-900', !match);
    });

    Object.entries(panes).forEach(([k, el]) => {
      if (el) el.classList.toggle('hidden', k !== tabKey);
    });

    if (tabKey === 'renda') {
      renderizarGraficoRenda();
    }

    if (updateUrl) {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tabKey);
      window.history.replaceState({}, '', url.toString());
    }
  }

  tabBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      ativarAba(btn.dataset.tab, true);
    });
  });

  // Gráfico de Barras Chart.js para Renda Passiva
  function renderizarGraficoRenda() {
    const canvas = document.getElementById('grafico-renda-comparativa');
    if (!canvas || graficoComparativo) return;

    const ctx = canvas.getContext('2d');
    const meses = <?= json_encode(array_values($rendaPassiva['meses_nomes'])) ?>;
    const dadosAnoAtual = <?= json_encode($rendaPassiva['totais_ano_atual']) ?>;
    const dadosAnoAnterior = <?= json_encode($rendaPassiva['totais_ano_anterior']) ?>;

    graficoComparativo = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: meses,
        datasets: [
          {
            label: '<?= $rendaPassiva['ano_ativo'] ?>',
            data: dadosAnoAtual,
            backgroundColor: 'rgba(29, 78, 216, 0.85)',
            borderColor: 'rgba(29, 78, 216, 1)',
            borderWidth: 1,
            borderRadius: 6,
            barPercentage: 0.7,
            categoryPercentage: 0.8,
          },
          {
            label: '<?= $rendaPassiva['ano_anterior'] ?>',
            data: dadosAnoAnterior,
            backgroundColor: 'rgba(148, 163, 184, 0.65)',
            borderColor: 'rgba(148, 163, 184, 1)',
            borderWidth: 1,
            borderRadius: 6,
            barPercentage: 0.7,
            categoryPercentage: 0.8,
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' },
              color: '#334155',
              padding: 16,
              usePointStyle: true,
              pointStyle: 'rectRounded',
            }
          },
          tooltip: {
            backgroundColor: '#0f172a',
            titleFont: { family: 'Plus Jakarta Sans', size: 12, weight: '700' },
            bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
            padding: 10,
            cornerRadius: 8,
            callbacks: {
              label: function (context) {
                return ' ' + context.dataset.label + ': R$ ' + Number(context.raw || 0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
              }
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: { font: { family: 'Plus Jakarta Sans', size: 11 }, color: '#64748b' }
          },
          y: {
            grid: { color: 'rgba(226, 232, 240, 0.6)' },
            ticks: {
              font: { family: 'Plus Jakarta Sans', size: 11 },
              color: '#64748b',
              callback: function (val) {
                return 'R$ ' + Number(val).toLocaleString('pt-BR');
              }
            }
          }
        }
      }
    });
  }

  // Inicializar na aba correta
  const abaInicial = '<?= $abaAtiva ?>';
  if (abaInicial === 'renda') {
    setTimeout(renderizarGraficoRenda, 50);
  }
})();
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>
