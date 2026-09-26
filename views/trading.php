<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
  <?php require BASE_PATH . '/views/layout/wallet_tabs.php'; ?>

  <a href="<?= BASE_URL ?>/trading.php?action=cards<?= $carteiraSelecionada ? '&carteira=' . $carteiraSelecionada : '' ?>"
    class="btn-secondary inline-flex items-center gap-2 text-xs font-semibold py-2 px-3.5 shadow-sm">
    <i class="ph ph-squares-four text-sm"></i> Ver visão em cartões
  </a>
</div>

<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
  <div>
    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700">Mercado B3</p>
    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Gráfico Técnico Avançado</h2>
    <p class="mt-1 text-xs text-slate-500 font-medium">Consulte cotações históricas, indicadores técnicos e variação de ativos em tela cheia.</p>
  </div>
  <?php if (!empty($ativos)): ?>
  <form method="GET" class="flex w-full flex-col gap-2 sm:w-auto sm:flex-row sm:items-end">
    <?php if ($carteiraSelecionada): ?>
      <input type="hidden" name="carteira" value="<?= (int)$carteiraSelecionada ?>">
    <?php endif; ?>
    <div class="w-full sm:w-72">
      <label for="ticker" class="mb-1 block text-xs font-bold text-slate-700 uppercase tracking-wider">Ativo Selecionado</label>
      <select id="ticker" name="ticker" onchange="this.form.submit()"
        class="w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2 text-sm font-bold text-slate-900 outline-none transition focus:border-blue-600 focus:ring-2 focus:ring-blue-100 shadow-sm">
        <?php foreach ($ativos as $ativo): ?>
          <option value="<?= e($ativo['ticker']) ?>" <?= $ativoSelecionado && $ativoSelecionado['ticker'] === $ativo['ticker'] ? 'selected' : '' ?>>
            <?= e($ativo['ticker']) ?> · <?= e($ativo['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>
  <?php endif; ?>
</div>

<?php if (!$ativoSelecionado): ?>
  <section class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
      <i class="ph ph-chart-line-up text-2xl"></i>
    </div>
    <h2 class="mt-4 text-base font-bold text-slate-900">Nenhum ativo disponível</h2>
    <p class="mx-auto mt-1 max-w-md text-xs text-slate-500">Cadastre um ativo para visualizar seu gráfico técnico.</p>
    <a href="<?= BASE_URL ?>/ativos.php?action=create"
      class="mt-5 btn-primary inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider px-4 py-2.5">
      <i class="ph ph-plus text-sm"></i> Cadastrar Ativo
    </a>
  </section>
<?php else: ?>
  <section class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm">
    <div class="flex flex-col gap-4 border-b border-slate-100 bg-slate-50/50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
      <div class="flex items-center gap-3">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-blue-700 to-blue-500 text-white shadow-sm">
          <i class="ph ph-chart-candlestick text-xl"></i>
        </div>
        <div>
          <div class="flex items-center gap-2">
            <h2 class="font-extrabold text-slate-900 tracking-tight text-lg"><?= e($ativoSelecionado['ticker']) ?></h2>
            <span class="rounded px-2 py-0.5 text-[11px] font-bold uppercase bg-blue-50 text-blue-700 border border-blue-200/80">B3</span>
          </div>
          <p class="text-xs text-slate-500 font-medium"><?= e($ativoSelecionado['nome']) ?></p>
        </div>
      </div>
    </div>

    <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-3 sm:flex-row sm:items-center sm:justify-between bg-white">
      <p class="text-xs text-slate-400 font-medium flex items-center gap-1"><i class="ph ph-info text-slate-400"></i> Dados e gráficos integrados via TradingView Advanced Charts.</p>
      <div class="flex flex-wrap items-center gap-1.5" role="group" aria-label="Período visível">
        <span class="mr-1 text-xs font-bold text-slate-400 uppercase tracking-wider">Período</span>
        <?php foreach ([['value' => '1D', 'label' => '1D'], ['value' => '1M', 'label' => '1M'], ['value' => '6M', 'label' => '6M'], ['value' => '1Y', 'label' => '1A'], ['value' => '5Y', 'label' => '5A'], ['value' => 'ALL', 'label' => 'Tudo']] as $index => $periodo): ?>
          <button type="button" data-range="<?= $periodo['value'] ?>"
            class="trading-range rounded-md px-2.5 py-1 text-xs font-bold transition-all duration-150 <?= $index === 1 ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' ?>">
            <?= e($periodo['label']) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>

    <div id="tradingview-chart" class="h-[500px] w-full sm:h-[600px]" aria-label="Gráfico de preço de <?= e($ativoSelecionado['ticker']) ?>"></div>
  </section>
<?php endif; ?>

<?php if ($ativoSelecionado): ?>
<script>
(function () {
  const chartContainer = document.getElementById('tradingview-chart');
  const symbol = <?= json_encode('BMFBOVESPA:' . $ativoSelecionado['ticker'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  let range = '1M';

  function renderChart() {
    chartContainer.replaceChildren();

    const widget = document.createElement('div');
    widget.className = 'tradingview-widget-container__widget';
    widget.style.height = '0';
    widget.style.width = '100%';
    chartContainer.appendChild(widget);

    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
    script.async = true;
    script.text = JSON.stringify({
      autosize: true,
      symbol,
      interval: 'D',
      range,
      timezone: 'America/Sao_Paulo',
      theme: 'light',
      style: '1',
      locale: 'br',
      allow_symbol_change: false,
      calendar: false,
      hide_side_toolbar: false,
      support_host: 'https://www.tradingview.com',
    });
    chartContainer.appendChild(script);
  }

  document.querySelectorAll('.trading-range').forEach((button) => {
    button.addEventListener('click', () => {
      range = button.dataset.range;
      document.querySelectorAll('.trading-range').forEach((item) => {
        item.classList.remove('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-sm');
        item.classList.add('text-slate-500');
      });
      button.classList.add('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-sm');
      button.classList.remove('text-slate-500');
      renderChart();
    });
  });

  renderChart();
})();
</script>
<?php endif; ?>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>