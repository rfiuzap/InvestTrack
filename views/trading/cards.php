<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="space-y-4 mb-6">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <!-- Abas de Filtro de Carteira (Todas / Renato / Vicente) -->
    <?php require BASE_PATH . '/views/layout/wallet_tabs.php'; ?>

    <a href="<?= BASE_URL ?>/trading.php<?= $carteiraSelecionada ? '?carteira=' . $carteiraSelecionada : '' ?>"
      class="btn-secondary inline-flex items-center gap-2 self-start text-xs font-semibold py-2 px-3.5 shadow-sm sm:self-auto">
      <i class="ph ph-chart-line-up text-sm"></i> Gráfico Ampliado
    </a>
  </div>

  <div>
    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700">Painel de Mercado B3</p>
    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-900">Seus ativos em tempo real</h2>
    <p class="mt-1 text-xs text-slate-500 font-medium">
      Monitoramento instantâneo de ativos em custódia com quantidade maior que zero
      <?= $carteiraSelecionada ? 'na carteira selecionada' : 'nas carteiras' ?>.
    </p>
  </div>
</div>

<?php if (empty($ativos)): ?>
  <section class="rounded-2xl border border-dashed border-slate-300 bg-white/80 px-6 py-16 text-center shadow-sm">
    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
      <i class="ph ph-chart-line-up text-2xl"></i>
    </div>
    <h2 class="mt-4 text-base font-bold text-slate-900">Nenhum ativo com posição em carteira</h2>
    <p class="mx-auto mt-1 max-w-md text-xs text-slate-500">
      Esta carteira não possui ativos com quantidade maior que zero no momento.
    </p>
    <a href="<?= BASE_URL ?>/operacoes.php?action=create"
      class="mt-5 btn-primary inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider px-4 py-2.5">
      <i class="ph ph-plus text-sm"></i> Registrar Operação
    </a>
  </section>
<?php else: ?>
  <section class="grid grid-cols-1 gap-4 xl:grid-cols-4 lg:grid-cols-2">
    <?php foreach ($ativos as $index => $ativo): ?>
      <?php $cardId = 'trading-card-' . (int)$ativo['id']; ?>
      <article class="overflow-hidden rounded-2xl border border-slate-200/90 bg-white shadow-sm hover:shadow-md transition-shadow duration-200">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/50 px-4 py-3">
          <div class="min-w-0">
            <div class="flex items-center gap-2">
              <h3 class="font-extrabold text-slate-900 tracking-tight"><?= e($ativo['ticker']) ?></h3>
              <span class="rounded px-1.5 py-0.5 text-[10px] font-bold uppercase bg-slate-200/70 text-slate-600"><?= e($ativo['tipo']) ?></span>
            </div>
            <p class="truncate text-xs text-slate-500 font-medium" title="<?= e($ativo['nome']) ?>"><?= e($ativo['nome']) ?></p>
          </div>
          <div class="text-right shrink-0">
            <span class="text-xs font-bold text-slate-900"><?= number_format($ativo['quantidade'], 0, ',', '.') ?> <span class="text-[10px] font-normal text-slate-400">cotas</span></span>
            <p class="text-[11px] font-medium text-slate-500">PM: R$ <?= number_format($ativo['preco_medio'], 2, ',', '.') ?></p>
          </div>
          <a href="<?= BASE_URL ?>/trading.php?ticker=<?= urlencode($ativo['ticker']) ?><?= $carteiraSelecionada ? '&carteira=' . $carteiraSelecionada : '' ?>"
            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-900 transition-colors" title="Abrir gráfico ampliado" aria-label="Abrir gráfico ampliado de <?= e($ativo['ticker']) ?>">
            <i class="ph ph-arrow-square-out text-base"></i>
          </a>
        </div>
        <div class="flex items-center justify-between border-b border-slate-100 px-3 py-2 bg-white">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Histórico</span>
          <div class="flex gap-1" role="group" aria-label="Período de <?= e($ativo['ticker']) ?>">
            <?php foreach ([['value' => '1D', 'label' => 'Dia'], ['value' => '1M', 'label' => 'Mês'], ['value' => '3M', 'label' => '3M'], ['value' => '1Y', 'label' => 'Ano'], ['value' => 'ALL', 'label' => 'Tudo']] as $periodoIndex => $periodo): ?>
              <button type="button" data-card="<?= e($cardId) ?>" data-range="<?= $periodo['value'] ?>"
                class="card-range rounded-md px-2 py-0.5 text-[11px] font-bold transition-all duration-150 <?= $periodoIndex === 1 ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-sm' : 'text-slate-500 hover:bg-slate-100 hover:text-slate-900' ?>">
                <?= e($periodo['label']) ?>
              </button>
            <?php endforeach; ?>
          </div>
        </div>
        <div id="<?= e($cardId) ?>" class="h-64 w-full" aria-label="Gráfico de preço de <?= e($ativo['ticker']) ?>"></div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<?php if (!empty($ativos)): ?>
<script>
(function () {
  const cards = <?= json_encode(array_map(static fn (array $ativo): array => [
    'id' => 'trading-card-' . (int)$ativo['id'],
    'symbol' => 'BMFBOVESPA:' . $ativo['ticker'],
    'tendencia' => $ativo['tendencia'],
  ], $ativos), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

  function renderCard(card, range) {
    const container = document.getElementById(card.id);
    if (!container) return;
    container.replaceChildren();

    const widget = document.createElement('div');
    widget.className = 'tradingview-widget-container__widget';
    widget.style.height = '0';
    widget.style.width = '100%';
    container.appendChild(widget);

    const script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'https://s3.tradingview.com/external-embedding/embed-widget-advanced-chart.js';
    script.async = true;
    const positivo = card.tendencia === 'up';
    script.text = JSON.stringify({
      autosize: true,
      symbol: card.symbol,
      interval: 'D',
      range,
      timezone: 'America/Sao_Paulo',
      theme: 'light',
      style: '3',
      overrides: {
        'mainSeriesProperties.areaStyle.color1': positivo ? 'rgba(16, 185, 129, 0.28)' : 'rgba(244, 63, 94, 0.24)',
        'mainSeriesProperties.areaStyle.color2': positivo ? 'rgba(16, 185, 129, 0.02)' : 'rgba(244, 63, 94, 0.02)',
        'mainSeriesProperties.areaStyle.linecolor': positivo ? '#059669' : '#e11d48',
      },
      locale: 'br',
      allow_symbol_change: false,
      hide_side_toolbar: true,
      hide_top_toolbar: true,
      hide_legend: true,
      calendar: false,
      support_host: 'https://www.tradingview.com',
    });
    container.appendChild(script);
  }

  cards.forEach((card) => renderCard(card, '1M'));

  document.querySelectorAll('.card-range').forEach((button) => {
    button.addEventListener('click', () => {
      const cardId = button.dataset.card;
      document.querySelectorAll('.card-range[data-card="' + cardId + '"]').forEach((item) => {
        item.classList.remove('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-sm');
        item.classList.add('text-slate-500');
      });
      button.classList.add('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-sm');
      button.classList.remove('text-slate-500');
      renderCard(cards.find((card) => card.id === cardId), button.dataset.range);
    });
  });
})();
</script>
<?php endif; ?>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>