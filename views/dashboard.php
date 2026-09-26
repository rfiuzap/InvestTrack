<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="mb-5">
  <?php require BASE_PATH . '/views/layout/wallet_tabs.php'; ?>
</div>

<section class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 mb-6">
  <div class="kpi-card">
    <div class="kpi-rotulo">Patrimônio Total</div>
    <div class="kpi-valor text-slate-900">R$ <?= number_format($resumo['valor_atual_total'], 2, ',', '.') ?></div>
    <p class="mt-2 text-xs font-semibold text-slate-500 flex items-center justify-between">
      <span class="flex items-center gap-1.5">
        <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
        <?= $resumo['qtd_ativos_ativos'] ?> ativo(s) em carteira
      </span>
      <?php if (!empty($resumo['maior_ativo'])): ?>
        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider" title="Maior posição da carteira">
          Maior: <?= e($resumo['maior_ativo']) ?> (<?= number_format($resumo['concentracao_maxima_percent'], 1, ',', '.') ?>%)
        </span>
      <?php endif; ?>
    </p>
  </div>

  <div class="kpi-card">
    <div class="kpi-rotulo">Total Investido</div>
    <div class="kpi-valor text-slate-900">R$ <?= number_format($resumo['valor_investido_total'], 2, ',', '.') ?></div>
    <p class="mt-2 text-xs font-medium text-slate-400">Custo total de aquisição em custódia</p>
  </div>

  <?php $lucro = $resumo['lucro_nao_realizado_total']; $positivo = $lucro >= 0; ?>
  <div class="kpi-card">
    <div class="kpi-rotulo">Resultado Não Realizado</div>
    <div class="kpi-valor <?= $positivo ? 'text-emerald-600' : 'text-rose-600' ?>">
      R$ <?= number_format($lucro, 2, ',', '.') ?>
    </div>
    <div class="mt-2 flex items-center">
      <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold <?= $positivo ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' ?>">
        <i class="ph <?= $positivo ? 'ph-trend-up' : 'ph-trend-down' ?>"></i>
        <?= number_format($resumo['variacao_percentual_total'], 2, ',', '.') ?>%
      </span>
      <span class="ml-2 text-xs text-slate-400 font-medium">Variação das cotas</span>
    </div>
  </div>

  <?php $trPositivo = $resumo['total_return_total'] >= 0; ?>
  <div class="kpi-card">
    <div class="kpi-rotulo flex items-center justify-between">
      <span>Retorno Global (Total Return)</span>
      <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200/60 uppercase">Real</span>
    </div>
    <div class="kpi-valor <?= $trPositivo ? 'text-blue-700' : 'text-rose-600' ?>">
      R$ <?= number_format($resumo['total_return_total'], 2, ',', '.') ?>
    </div>
    <div class="mt-2 flex items-center">
      <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold <?= $trPositivo ? 'bg-blue-50 text-blue-700 border border-blue-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' ?>">
        <i class="ph <?= $trPositivo ? 'ph-trend-up' : 'ph-trend-down' ?>"></i>
        <?= number_format($resumo['total_return_percentual'], 2, ',', '.') ?>%
      </span>
      <span class="ml-2 text-xs text-slate-400 font-medium">Cotas + Proventos + Vendas</span>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-rotulo">Yield on Cost Médio (YoC)</div>
    <div class="kpi-valor text-emerald-600">
      <?= number_format($resumo['yoc_medio'], 2, ',', '.') ?><span class="kpi-unidade font-bold ml-1">%</span>
    </div>
    <p class="mt-2 text-xs font-medium text-slate-400">Rendimento de dividendos s/ custo histórico</p>
  </div>

  <div class="kpi-card">
    <div class="kpi-rotulo">Proventos & Renda Média</div>
    <div class="kpi-valor text-slate-900">R$ <?= number_format($resumo['total_proventos'], 2, ',', '.') ?></div>
    <p class="mt-2 text-xs font-medium text-slate-500 flex items-center gap-1.5">
      <i class="ph ph-trend-up text-emerald-600 font-bold"></i>
      Média 12m: <strong class="text-slate-800">R$ <?= number_format($resumo['media_mensal_proventos'], 2, ',', '.') ?>/mês</strong>
    </p>
  </div>
</section>

<section class="grid grid-cols-1 xl:grid-cols-2 gap-4 mb-6">
  <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow duration-200">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
        <i class="ph ph-chart-line text-blue-700 text-base"></i> Evolução do Investimento
      </h2>
    </div>
    <canvas id="chart-evolucao" height="110"></canvas>
  </div>
  <div class="bg-white border border-slate-200 rounded-xl shadow-sm p-5 hover:shadow-md transition-shadow duration-200">
    <h2 class="text-sm font-bold text-slate-900 tracking-tight mb-4 flex items-center gap-2">
      <i class="ph ph-chart-bar text-blue-700 text-base"></i> Alocação da Carteira
    </h2>
    <div class="h-[280px]">
      <canvas id="chart-alocacao"></canvas>
    </div>
  </div>
</section>

<section class="tabela-card">
  <div class="flex items-center justify-between p-5 border-b border-slate-100">
    <h2 class="text-sm font-bold text-slate-900 tracking-tight flex items-center gap-2">
      <i class="ph ph-briefcase text-blue-700 text-base"></i> Posição por Ativo
    </h2>
    <a href="<?= BASE_URL ?>/ativos.php" class="text-xs font-semibold text-blue-700 hover:text-blue-800 transition-colors">Ver todos →</a>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm js-sortable">
      <thead>
        <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
          <th class="py-3 px-4" data-sort-type="text">Ativo</th>
          <th class="py-3 px-4">Qtd.</th>
          <th class="py-3 px-4">Preço de Compra</th>
          <th class="py-3 px-4">Valor Investido</th>
          <th class="py-3 px-4">Cotação recente</th>
          <th class="py-3 px-4">Valor Atualizado</th>
          <th class="py-3 px-4">Resultado (R$)</th>
          <th class="py-3 px-4">Variação (%)</th>
          <th class="py-3 px-4">YoC %</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php $posicoesAtivas = array_filter($resumo['posicoes'], fn($p) => $p['quantidade'] > 0); ?>
        <?php if (empty($posicoesAtivas)): ?>
          <tr><td colspan="9" class="py-8 text-center text-slate-400">Nenhum ativo em carteira. Registre sua primeira operação.</td></tr>
        <?php endif; ?>
        <?php foreach ($posicoesAtivas as $p): 
          $pos = $p['lucro_nao_realizado'] >= 0; 
          $valorInvestido = $p['quantidade'] * $p['preco_medio'];
        ?>
        <tr class="hover:bg-slate-50/80 transition-colors">
          <td class="py-3.5 px-4" data-sort-value="<?= e($p['ativo']['ticker']) ?>">
            <span class="font-bold text-slate-900 tracking-tight"><?= e($p['ativo']['ticker']) ?></span>
            <p class="text-xs text-slate-500"><?= e($p['ativo']['nome']) ?></p>
          </td>
          <td class="py-3.5 px-4 text-slate-700 font-medium" data-sort-value="<?= (float)$p['quantidade'] ?>"><?= number_format($p['quantidade'], 0, ',', '.') ?></td>
          <td class="py-3.5 px-4 text-slate-700 font-medium" data-sort-value="<?= (float)$p['preco_medio'] ?>">R$ <?= number_format($p['preco_medio'], 2, ',', '.') ?></td>
          <td class="py-3.5 px-4 text-slate-700 font-medium" data-sort-value="<?= (float)$valorInvestido ?>">R$ <?= number_format($valorInvestido, 2, ',', '.') ?></td>
          <td class="py-3.5 px-4 text-slate-700 font-medium" data-sort-value="<?= (float)$p['cotacao_atual'] ?>">R$ <?= number_format($p['cotacao_atual'], 2, ',', '.') ?></td>
          <td class="py-3.5 px-4 text-slate-900 font-bold" data-sort-value="<?= (float)$p['valor_atual'] ?>">R$ <?= number_format($p['valor_atual'], 2, ',', '.') ?></td>
          <td class="py-3.5 px-4" data-sort-value="<?= (float)$p['lucro_nao_realizado'] ?>">
            <span class="inline-flex items-center gap-1 rounded-md px-2.5 py-1 text-xs font-bold <?= $pos ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' ?>">
              R$ <?= number_format($p['lucro_nao_realizado'], 2, ',', '.') ?>
            </span>
          </td>
          <td class="py-3.5 px-4" data-sort-value="<?= (float)$p['variacao_percentual'] ?>">
            <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold <?= $pos ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' ?>">
              <i class="ph <?= $pos ? 'ph-trend-up' : 'ph-trend-down' ?>"></i>
              <?= ($pos ? '+' : '') . number_format($p['variacao_percentual'], 2, ',', '.') ?>%
            </span>
          </td>
          <td class="py-3.5 px-4" data-sort-value="<?= (float)$p['yoc'] ?>">
            <span class="text-xs font-bold <?= (float)$p['yoc'] > 0 ? 'text-emerald-700' : 'text-slate-400' ?>">
              <?= number_format((float)$p['yoc'], 2, ',', '.') ?>%
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<script>
  window.__ALOCACAO__ = <?= json_encode($alocacao, JSON_UNESCAPED_UNICODE) ?>;
  window.__EVOLUCAO__ = <?= json_encode($evolucao, JSON_UNESCAPED_UNICODE) ?>;
</script>
<?php $extraScripts = '<script src="' . BASE_URL . '/assets/js/charts.js"></script>'; ?>
<?php require BASE_PATH . '/views/layout/footer.php'; ?>
