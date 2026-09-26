<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div class="flex flex-wrap items-center gap-2">
    <?php require BASE_PATH . '/views/layout/wallet_tabs.php'; ?>

    <!-- Filtro Tipo de Provento (Todos / Dividendo / JCP / Rendimento) -->
    <div class="inline-flex items-center gap-1 rounded-xl border border-slate-200/80 bg-white/75 backdrop-blur-md p-1 shadow-sm" id="filtro-tipo-container">
      <button type="button" data-tipo="TODOS"
        class="filtro-tipo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_4px_12px_rgba(29,78,216,0.22)]">
        Todos
      </button>
      <button type="button" data-tipo="DIVIDENDO"
        class="filtro-tipo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 text-slate-600 hover:bg-white hover:text-slate-900">
        Dividendo
      </button>
      <button type="button" data-tipo="JCP"
        class="filtro-tipo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 text-slate-600 hover:bg-white hover:text-slate-900">
        JCP
      </button>
      <button type="button" data-tipo="RENDIMENTO"
        class="filtro-tipo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 text-slate-600 hover:bg-white hover:text-slate-900">
        Rendimento
      </button>
    </div>

    <!-- Filtro Período Temporal -->
    <div class="inline-flex items-center gap-1 rounded-xl border border-slate-200/80 bg-white/75 backdrop-blur-md p-1 shadow-sm" id="filtro-periodo-container">
      <button type="button" data-periodo="TODOS"
        class="filtro-periodo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_4px_12px_rgba(29,78,216,0.22)]">
        Todas as Datas
      </button>
      <button type="button" data-periodo="MES_ATUAL"
        class="filtro-periodo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 text-slate-600 hover:bg-white hover:text-slate-900">
        Este Mês
      </button>
      <button type="button" data-periodo="ANO_ATUAL"
        class="filtro-periodo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 text-slate-600 hover:bg-white hover:text-slate-900">
        Ano Atual
      </button>
    </div>
  </div>

  <div class="flex items-center gap-2">
    <div class="relative">
      <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input type="text" id="filtro-ticker-input" placeholder="Filtrar por ticker..."
        class="w-56 rounded-xl border border-slate-200 bg-white pl-9 pr-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none shadow-sm transition-all">
    </div>
    <button type="button" onclick="exportarTabelaCSV('tabela-proventos', 'proventos_investtrack')"
      class="btn-secondary inline-flex items-center gap-1.5 text-xs font-bold py-2 px-3 shadow-sm" title="Exportar proventos para CSV (Excel)">
      <i class="ph ph-file-csv text-base text-emerald-600"></i> Exportar
    </button>
  </div>
</div>

<div class="flex items-center justify-between mb-4">
  <p class="text-xs text-slate-500 font-medium">Histórico de dividendos, JCP e rendimentos recebidos por ativo e carteira.</p>
  <a href="<?= BASE_URL ?>/proventos.php?action=create"
    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-600 py-2.5 px-4 text-xs font-bold uppercase tracking-wider text-white shadow-[0_4px_14px_rgba(29,78,216,0.3)] hover:shadow-[0_8px_24px_rgba(29,78,216,0.45)] hover:-translate-y-0.5 active:translate-y-0 transition-all duration-150">
    <i class="ph ph-plus-circle text-base"></i> Novo Provento
  </a>
</div>

<div class="tabela-card">
  <table class="w-full text-sm js-sortable" id="tabela-proventos">
    <thead>
      <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
        <th class="py-3 px-4" data-default-sort="desc">Data</th>
        <th class="py-3 px-4">Carteira</th>
        <th class="py-3 px-4" data-sort-type="text">Ativo</th>
        <th class="py-3 px-4">Tipo</th>
        <th class="py-3 px-4">Valor</th>
        <th class="py-3 px-4">Observação</th>
        <th class="py-3 px-4 text-right" data-no-sort data-no-export>Ações</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php if (empty($proventos)): ?>
        <tr id="sem-proventos"><td colspan="7" class="py-8 text-center text-slate-400">Nenhum provento registrado.</td></tr>
      <?php endif; ?>
      <?php foreach ($proventos as $p): ?>
      <tr class="hover:bg-slate-50/80 cursor-pointer transition-colors provento-row"
          onclick="window.location.href='<?= BASE_URL ?>/proventos.php?action=edit&id=<?= (int)$p['id'] ?>'"
          title="Clique para editar provento"
          data-ticker="<?= e($p['ticker']) ?>"
          data-tipo="<?= e($p['tipo']) ?>"
          data-data="<?= e($p['data_pagamento']) ?>">
        <td class="py-3.5 px-4 text-slate-600 font-medium" data-sort-value="<?= e($p['data_pagamento']) ?>"><?= date('d/m/Y', strtotime($p['data_pagamento'])) ?></td>
        <td class="py-3.5 px-4">
          <?= carteira_badge($p['carteira_nome']) ?>
        </td>
        <td class="py-3.5 px-4 font-bold text-slate-900 tracking-tight" data-sort-value="<?= e($p['ticker']) ?>"><?= e($p['ticker']) ?></td>
        <td class="py-3.5 px-4">
          <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold bg-violet-50 text-violet-700 border border-violet-200/80"><?= e($p['tipo']) ?></span>
        </td>
        <td class="py-3.5 px-4 text-emerald-600 font-bold">R$ <?= number_format($p['valor'], 2, ',', '.') ?></td>
        <td class="py-3.5 px-4 text-slate-500 text-xs"><?= e($p['observacao'] ?? '') ?></td>
        <td class="py-3.5 px-4" onclick="event.stopPropagation()">
          <div class="flex items-center justify-end gap-1.5">
            <a href="<?= BASE_URL ?>/proventos.php?action=edit&id=<?= (int)$p['id'] ?>"
              class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-900 transition-colors" title="Editar">
              <i class="ph ph-pencil-simple text-base"></i>
            </a>
            <form method="POST" action="<?= BASE_URL ?>/proventos.php?action=delete" onsubmit="return confirm('Deseja realmente excluir este provento?');" class="m-0">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
              <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Excluir">
                <i class="ph ph-trash text-base"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <tr id="sem-resultados-filtro" class="hidden">
        <td colspan="7" class="py-8 text-center text-slate-400">Nenhum provento encontrado com os filtros selecionados.</td>
      </tr>
    </tbody>
  </table>
</div>

<script>
(function () {
  const tipoBtns = document.querySelectorAll('#filtro-tipo-container .filtro-tipo-btn');
  const periodoBtns = document.querySelectorAll('#filtro-periodo-container .filtro-periodo-btn');
  const tickerInput = document.getElementById('filtro-ticker-input');
  const rows = document.querySelectorAll('#tabela-proventos tbody tr.provento-row');
  const emptyFilterRow = document.getElementById('sem-resultados-filtro');

  let tipoSelecionado = 'TODOS';
  let periodoSelecionado = 'TODOS';

  const hoje = new Date();
  const mesAtualStr = hoje.toISOString().slice(0, 7); // 'YYYY-MM'
  const anoAtualStr = hoje.toISOString().slice(0, 4); // 'YYYY'

  function filtrarTabela() {
    const termo = (tickerInput?.value || '').trim().toLowerCase();
    let visiveis = 0;

    rows.forEach(row => {
      const rowTipo = row.dataset.tipo || '';
      const rowData = row.dataset.data || '';
      const rowTicker = (row.dataset.ticker || '').toLowerCase();

      // 1. Tipo (TODOS / DIVIDENDO / JCP / RENDIMENTO)
      const matchTipo = (tipoSelecionado === 'TODOS' || rowTipo === tipoSelecionado);

      // 2. Período
      let matchPeriodo = true;
      if (periodoSelecionado === 'MES_ATUAL') {
        matchPeriodo = rowData.startsWith(mesAtualStr);
      } else if (periodoSelecionado === 'ANO_ATUAL') {
        matchPeriodo = rowData.startsWith(anoAtualStr);
      }

      // 3. Ticker
      const matchTicker = (!termo || rowTicker.includes(termo));

      if (matchTipo && matchPeriodo && matchTicker) {
        row.classList.remove('hidden');
        visiveis++;
      } else {
        row.classList.add('hidden');
      }
    });

    if (emptyFilterRow) {
      emptyFilterRow.classList.toggle('hidden', visiveis > 0 || rows.length === 0);
    }
  }

  // Eventos Tipo
  tipoBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      tipoBtns.forEach(b => {
        b.classList.remove('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-[0_4px_12px_rgba(29,78,216,0.22)]');
        b.classList.add('text-slate-600', 'hover:bg-white', 'hover:text-slate-900');
      });
      btn.classList.add('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-[0_4px_12px_rgba(29,78,216,0.22)]');
      btn.classList.remove('text-slate-600', 'hover:bg-white', 'hover:text-slate-900');
      tipoSelecionado = btn.dataset.tipo || 'TODOS';
      filtrarTabela();
    });
  });

  // Eventos Período
  periodoBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      periodoBtns.forEach(b => {
        b.classList.remove('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-[0_4px_12px_rgba(29,78,216,0.22)]');
        b.classList.add('text-slate-600', 'hover:bg-white', 'hover:text-slate-900');
      });
      btn.classList.add('bg-gradient-to-r', 'from-blue-700', 'to-blue-600', 'text-white', 'shadow-[0_4px_12px_rgba(29,78,216,0.22)]');
      btn.classList.remove('text-slate-600', 'hover:bg-white', 'hover:text-slate-900');
      periodoSelecionado = btn.dataset.periodo || 'TODOS';
      filtrarTabela();
    });
  });

  tickerInput?.addEventListener('input', filtrarTabela);
})();
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>
