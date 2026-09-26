<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div class="flex flex-wrap items-center gap-2">
    <?php require BASE_PATH . '/views/layout/wallet_tabs.php'; ?>

    <!-- Filtro Tipo de Operação (Todas / Compra / Venda) -->
    <div class="inline-flex items-center gap-1 rounded-xl border border-slate-200/80 bg-white/75 backdrop-blur-md p-1 shadow-sm" id="filtro-tipo-container">
      <button type="button" data-tipo="TODOS"
        class="filtro-tipo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_4px_12px_rgba(29,78,216,0.22)]">
        Todas
      </button>
      <button type="button" data-tipo="COMPRA"
        class="filtro-tipo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 text-slate-600 hover:bg-white hover:text-slate-900">
        Compra
      </button>
      <button type="button" data-tipo="VENDA"
        class="filtro-tipo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 text-slate-600 hover:bg-white hover:text-slate-900">
        Venda
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
      <button type="button" data-periodo="TRES_MESES"
        class="filtro-periodo-btn rounded-lg px-3 py-1.5 text-xs font-semibold transition-all duration-150 text-slate-600 hover:bg-white hover:text-slate-900">
        3 Meses
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
    <a href="<?= BASE_URL ?>/operacoes.php?action=import"
      class="btn-secondary inline-flex items-center gap-1.5 text-xs font-bold py-2 px-3 shadow-sm" title="Importar operações via planilha CSV (CEI / B3 / Manual)">
      <i class="ph ph-upload-simple text-base text-blue-600"></i> Importar CSV
    </a>
    <button type="button" onclick="exportarTabelaCSV('tabela-operacoes', 'operacoes_investtrack')"
      class="btn-secondary inline-flex items-center gap-1.5 text-xs font-bold py-2 px-3 shadow-sm" title="Exportar operações para CSV (Excel)">
      <i class="ph ph-file-csv text-base text-emerald-600"></i> Exportar
    </button>
  </div>
</div>

<div class="flex items-center justify-between mb-4">
  <p class="text-xs text-slate-500 font-medium">Histórico de compras e vendas ordenado pela data mais recente. Clique em qualquer linha para editar.</p>
  <a href="<?= BASE_URL ?>/operacoes.php?action=create"
    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-700 via-blue-600 to-indigo-600 py-2.5 px-4 text-xs font-bold uppercase tracking-wider text-white shadow-[0_4px_14px_rgba(29,78,216,0.3)] hover:shadow-[0_8px_24px_rgba(29,78,216,0.45)] hover:-translate-y-0.5 active:translate-y-0 transition-all duration-150">
    <i class="ph ph-plus-circle text-base"></i> Nova Operação
  </a>
</div>

<div class="tabela-card">
  <table class="w-full text-sm js-sortable" id="tabela-operacoes">
    <thead>
      <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
        <th class="py-3 px-4" data-default-sort="desc">Data</th>
        <th class="py-3 px-4">Carteira</th>
        <th class="py-3 px-4" data-sort-type="text">Ativo</th>
        <th class="py-3 px-4">Tipo</th>
        <th class="py-3 px-4">Qtd.</th>
        <th class="py-3 px-4">Preço Unit.</th>
        <th class="py-3 px-4">Taxas</th>
        <th class="py-3 px-4">Total</th>
        <th class="py-3 px-2 w-10 text-center" data-no-sort data-no-export></th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php if (empty($transacoes)): ?>
        <tr id="sem-operacoes"><td colspan="9" class="py-8 text-center text-slate-400">Nenhuma operação registrada.</td></tr>
      <?php endif; ?>
      <?php foreach ($transacoes as $t): $total = $t['quantidade'] * $t['preco_unitario'] + ($t['tipo'] === 'COMPRA' ? $t['taxas'] : -$t['taxas']); ?>
      <tr class="hover:bg-slate-50/80 cursor-pointer transition-colors group operacao-row"
          data-ticker="<?= e($t['ticker']) ?>"
          data-tipo="<?= e($t['tipo']) ?>"
          data-data="<?= e($t['data_operacao']) ?>"
          title="Clique para editar"
          onclick="window.location.href='<?= BASE_URL ?>/operacoes.php?action=edit&id=<?= (int)$t['id'] ?>'">
        <td class="py-3.5 px-4 text-slate-600 font-medium" data-sort-value="<?= e($t['data_operacao']) ?>"><?= date('d/m/Y', strtotime($t['data_operacao'])) ?></td>
        <td class="py-3.5 px-4">
          <?= carteira_badge($t['carteira_nome']) ?>
        </td>
        <td class="py-3.5 px-4 font-bold text-slate-900 tracking-tight" data-sort-value="<?= e($t['ticker']) ?>"><?= e($t['ticker']) ?></td>
        <td class="py-3.5 px-4">
          <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold <?= $t['tipo'] === 'COMPRA' ? 'bg-blue-50 text-blue-700 border border-blue-200/80' : 'bg-amber-50 text-amber-700 border border-amber-200/80' ?>">
            <?= $t['tipo'] === 'COMPRA' ? 'Compra' : 'Venda' ?>
          </span>
        </td>
        <td class="py-3.5 px-4 text-slate-700 font-medium"><?= number_format($t['quantidade'], 0, ',', '.') ?></td>
        <td class="py-3.5 px-4 text-slate-700 font-medium">R$ <?= formatar_preco_unitario($t['preco_unitario']) ?></td>
        <td class="py-3.5 px-4 text-slate-500 font-medium">R$ <?= number_format($t['taxas'], 2, ',', '.') ?></td>
        <td class="py-3.5 px-4 text-slate-900 font-bold">R$ <?= number_format($total, 2, ',', '.') ?></td>
        <td class="py-3.5 px-2 text-center" onclick="event.stopPropagation()">
          <button type="button"
            onclick="abrirModalExclusao(<?= (int)$t['id'] ?>, '<?= e($t['ticker']) ?>', '<?= date('d/m/Y', strtotime($t['data_operacao'])) ?>')"
            class="rounded-lg p-1.5 text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
            title="Excluir operação">
            <i class="ph ph-trash text-base"></i>
          </button>
        </td>
      </tr>
      <?php endforeach; ?>
      <tr id="sem-resultados-filtro" class="hidden">
        <td colspan="9" class="py-8 text-center text-slate-400">Nenhuma operação encontrada com os filtros selecionados.</td>
      </tr>
    </tbody>
  </table>
</div>

<!-- Modal Popup de Certeza para Exclusão -->
<div id="modal-exclusao" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 transition-opacity">
  <div class="bg-white/95 backdrop-blur-md rounded-2xl border border-slate-200/80 shadow-2xl max-w-sm w-full p-6 text-center transform transition-all animate-in fade-in zoom-in-95 duration-150">
    <div class="mx-auto w-12 h-12 rounded-full bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center mb-4">
      <i class="ph ph-warning text-2xl"></i>
    </div>
    <h3 class="text-base font-bold text-slate-900 mb-2">Excluir Operação?</h3>
    <p class="text-xs text-slate-500 mb-6 leading-relaxed">
      Tem certeza que deseja excluir a operação de <strong id="modal-operacao-ticker" class="text-slate-800"></strong> realizada em <span id="modal-operacao-data" class="text-slate-800"></span>?<br>Esta ação não poderá ser desfeita.
    </p>
    <div class="flex items-center justify-center gap-3">
      <button type="button" onclick="fecharModalExclusao()" class="btn-secondary flex-1 py-2 text-xs">
        Cancelar
      </button>
      <form method="POST" action="<?= BASE_URL ?>/operacoes.php?action=delete" class="flex-1 m-0">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="modal-operacao-id" value="">
        <button type="submit" class="w-full rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs py-2.5 shadow-sm transition-colors">
          Sim, excluir
        </button>
      </form>
    </div>
  </div>
</div>

<script>
function abrirModalExclusao(id, ticker, data) {
  document.getElementById('modal-operacao-id').value = id;
  document.getElementById('modal-operacao-ticker').textContent = ticker;
  document.getElementById('modal-operacao-data').textContent = data;
  document.getElementById('modal-exclusao').classList.remove('hidden');
}

function fecharModalExclusao() {
  document.getElementById('modal-exclusao').classList.add('hidden');
}

document.getElementById('modal-exclusao')?.addEventListener('click', (e) => {
  if (e.target.id === 'modal-exclusao') {
    fecharModalExclusao();
  }
});

document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    fecharModalExclusao();
  }
});

// Filtro integrado: Tipo de Operação, Período e Busca por Ticker
(function () {
  const tipoBtns = document.querySelectorAll('#filtro-tipo-container .filtro-tipo-btn');
  const periodoBtns = document.querySelectorAll('#filtro-periodo-container .filtro-periodo-btn');
  const tickerInput = document.getElementById('filtro-ticker-input');
  const rows = document.querySelectorAll('#tabela-operacoes tbody tr.operacao-row');
  const emptyFilterRow = document.getElementById('sem-resultados-filtro');

  let tipoSelecionado = 'TODOS';
  let periodoSelecionado = 'TODOS';

  const hoje = new Date();
  const mesAtualStr = hoje.toISOString().slice(0, 7); // 'YYYY-MM'
  const anoAtualStr = hoje.toISOString().slice(0, 4); // 'YYYY'
  const tresMesesAtras = new Date(hoje.getFullYear(), hoje.getMonth() - 2, 1).toISOString().slice(0, 10);

  function filtrarTabela() {
    const termo = (tickerInput?.value || '').trim().toLowerCase();
    let visiveis = 0;

    rows.forEach(row => {
      const rowTipo = row.dataset.tipo || '';
      const rowData = row.dataset.data || '';
      const rowTicker = (row.dataset.ticker || '').toLowerCase();

      // 1. Tipo (COMPRA / VENDA / TODOS)
      const matchTipo = (tipoSelecionado === 'TODOS' || rowTipo === tipoSelecionado);

      // 2. Período
      let matchPeriodo = true;
      if (periodoSelecionado === 'MES_ATUAL') {
        matchPeriodo = rowData.startsWith(mesAtualStr);
      } else if (periodoSelecionado === 'TRES_MESES') {
        matchPeriodo = rowData >= tresMesesAtras;
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
