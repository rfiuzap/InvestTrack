<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
  <div class="flex flex-wrap items-center gap-2">
    <?php require BASE_PATH . '/views/layout/wallet_tabs.php'; ?>

    <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer select-none bg-white/75 backdrop-blur-md px-3 py-1.5 rounded-xl border border-slate-200/80 shadow-sm">
      <span class="relative inline-flex h-5 w-9 shrink-0 items-center">
        <input type="checkbox" id="toggle-mostrar-zerados" class="peer sr-only">
        <span class="absolute inset-0 rounded-full bg-slate-200 transition-colors peer-checked:bg-blue-600 pointer-events-none"></span>
        <span class="absolute left-0.5 h-4 w-4 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-4 pointer-events-none"></span>
      </span>
      Mostrar sem posição
    </label>
  </div>

  <div class="flex items-center gap-2">
    <div class="relative">
      <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
      <input type="text" id="filtro-ticker-ativos" placeholder="Filtrar ticker ou nome..."
        class="w-56 rounded-xl border border-slate-200 bg-white pl-9 pr-3.5 py-2 text-xs font-medium text-slate-800 placeholder-slate-400 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none shadow-sm transition-all">
    </div>
    <button type="button" onclick="exportarTabelaCSV('tabela-ativos', 'ativos_investtrack')"
      class="btn-secondary inline-flex items-center gap-1.5 text-xs font-bold py-2 px-3 shadow-sm" title="Exportar ativos para CSV (Excel)">
      <i class="ph ph-file-csv text-base text-emerald-600"></i> Exportar
    </button>
  </div>
</div>

<div class="flex items-center justify-between mb-4">
  <p class="text-xs text-slate-500 font-medium">Gerencie os ativos da sua carteira B3 com cotações automáticas e preços médios.</p>
  <div class="flex items-center gap-2">
    <button id="btn-atualizar-cotacoes" type="button"
      class="btn-secondary inline-flex items-center gap-2 text-xs font-semibold py-2 px-3.5 shadow-sm">
      <i class="ph ph-arrows-clockwise text-sm"></i> Atualizar Cotações
    </button>
    <a href="<?= BASE_URL ?>/ativos.php?action=create"
      class="btn-primary inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider py-2 px-3.5">
      <i class="ph ph-plus text-sm"></i> Novo Ativo
    </a>
  </div>
</div>

<div class="tabela-card">
  <table class="w-full text-sm js-sortable" id="tabela-ativos">
    <thead>
      <tr class="text-left text-[11px] font-bold text-slate-500 uppercase tracking-wider bg-slate-50/80 border-b border-slate-200">
        <th class="py-3 px-4" data-sort-type="text">Ticker</th>
        <th class="py-3 px-4" data-sort-type="text">Nome</th>
        <th class="py-3 px-4">Tipo</th>
        <th class="py-3 px-4">Qtd.</th>
        <th class="py-3 px-4">Preço Médio</th>
        <th class="py-3 px-4">Cotação</th>
        <th class="py-3 px-4">Resultado</th>
        <th class="py-3 px-4">Variação</th>
        <th class="py-3 px-4 text-right" data-no-sort data-no-export>Ações</th>
      </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
      <?php if (empty($posicoes)): ?>
        <tr id="sem-ativos"><td colspan="9" class="py-8 text-center text-slate-400">Nenhum ativo cadastrado.</td></tr>
      <?php endif; ?>
      <?php foreach ($posicoes as $p): $pos = $p['lucro_nao_realizado'] >= 0; ?>
      <tr class="hover:bg-slate-50/80 cursor-pointer transition-colors ativo-row"
          onclick="window.location.href='<?= BASE_URL ?>/ativos.php?action=edit&id=<?= (int)$p['ativo']['id'] ?>'"
          title="Clique para editar ativo"
          data-ticker="<?= e($p['ativo']['ticker']) ?>"
          data-nome="<?= e($p['ativo']['nome']) ?>"
          data-quantidade="<?= (float)$p['quantidade'] ?>">
        <td class="py-3.5 px-4 font-bold text-slate-900 tracking-tight" data-ativo-id="<?= (int)$p['ativo']['id'] ?>" data-sort-value="<?= e($p['ativo']['ticker']) ?>"><?= e($p['ativo']['ticker']) ?></td>
        <td class="py-3.5 px-4 text-slate-600 font-medium" data-sort-value="<?= e($p['ativo']['nome']) ?>"><?= e($p['ativo']['nome']) ?></td>
        <td class="py-3.5 px-4">
          <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold uppercase bg-slate-100 text-slate-600 border border-slate-200/80">
            <?= e($p['ativo']['tipo']) ?>
          </span>
        </td>
        <td class="py-3.5 px-4 text-slate-700 font-medium"><?= number_format($p['quantidade'], 0, ',', '.') ?></td>
        <td class="py-3.5 px-4 text-slate-700 font-medium">R$ <?= number_format($p['preco_medio'], 2, ',', '.') ?></td>
        <td class="py-3.5 px-4 text-slate-700 font-medium quote-cell">R$ <?= number_format($p['cotacao_atual'], 2, ',', '.') ?></td>
        <td class="py-3.5 px-4">
          <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold <?= $pos ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' ?>">
            R$ <?= number_format($p['lucro_nao_realizado'], 2, ',', '.') ?>
          </span>
        </td>
        <td class="py-3.5 px-4">
          <span class="inline-flex items-center gap-1 rounded-md px-2 py-0.5 text-xs font-bold <?= $pos ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/80' : 'bg-rose-50 text-rose-700 border border-rose-200/80' ?>">
            <i class="ph <?= $pos ? 'ph-trend-up' : 'ph-trend-down' ?>"></i>
            <?= number_format($p['variacao_percentual'], 2, ',', '.') ?>%
          </span>
        </td>
        <td class="py-3.5 px-4" onclick="event.stopPropagation()">
          <div class="flex items-center justify-end gap-1.5">
            <a href="<?= BASE_URL ?>/ativos.php?action=edit&id=<?= (int)$p['ativo']['id'] ?>"
              class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-900 transition-colors" title="Editar">
              <i class="ph ph-pencil-simple text-base"></i>
            </a>
            <form method="POST" action="<?= BASE_URL ?>/ativos.php?action=delete" onsubmit="return confirm('Deseja realmente excluir este ativo?');" class="m-0">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int)$p['ativo']['id'] ?>">
              <button type="submit" class="rounded-lg p-1.5 text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Excluir">
                <i class="ph ph-trash text-base"></i>
              </button>
            </form>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      <tr id="sem-resultados-filtro" class="hidden">
        <td colspan="9" class="py-8 text-center text-slate-400">Nenhum ativo encontrado com os filtros selecionados.</td>
      </tr>
    </tbody>
  </table>
</div>

<script>
(function () {
  const searchInput = document.getElementById('filtro-ticker-ativos');
  const toggleZerados = document.getElementById('toggle-mostrar-zerados');
  const rows = document.querySelectorAll('#tabela-ativos tbody tr.ativo-row');
  const emptyFilterRow = document.getElementById('sem-resultados-filtro');

  function aplicarFiltros() {
    const termo = (searchInput?.value || '').trim().toLowerCase();
    const mostrarZerados = toggleZerados ? toggleZerados.checked : false;
    let visiveis = 0;

    rows.forEach((row) => {
      const ticker = (row.dataset.ticker || '').toLowerCase();
      const nome = (row.dataset.nome || '').toLowerCase();
      const quantidade = parseFloat(row.dataset.quantidade || '0');

      // 1. Termo de busca (ticker ou nome)
      const matchBusca = (!termo || ticker.includes(termo) || nome.includes(termo));

      // 2. Posição zerada
      const matchPosicao = mostrarZerados || quantidade > 0;

      if (matchBusca && matchPosicao) {
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

  searchInput?.addEventListener('input', aplicarFiltros);
  toggleZerados?.addEventListener('change', aplicarFiltros);

  aplicarFiltros();
})();
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>
