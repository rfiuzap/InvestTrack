<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="max-w-xl bg-white border border-slate-200/90 rounded-2xl shadow-sm p-7">
  <div class="mb-6 pb-4 border-b border-slate-100">
    <h2 class="text-base font-extrabold text-slate-900 tracking-tight"><?= $provento ? 'Editar Provento' : 'Novo Provento' ?></h2>
    <p class="text-xs text-slate-500 font-medium mt-0.5"><?= $provento ? 'Atualize as informações do provento recebido.' : 'Registre dividendos, JCP ou rendimentos da carteira.' ?></p>
  </div>
  <form method="POST">
    <?= csrf_field() ?>
    <div class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ativo</label>
        <select name="ativo_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
          <option value="">Selecione o ativo...</option>
          <?php foreach ($ativos as $a): ?>
            <option value="<?= (int)$a['id'] ?>" data-tipo="<?= e($a['tipo']) ?>" <?= (isset($provento['ativo_id']) && (int)$provento['ativo_id'] === (int)$a['id']) ? 'selected' : '' ?>>
              <?= e($a['ticker']) ?> - <?= e($a['nome']) ?> (<?= e($a['tipo']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Carteira</label>
        <?php $carteiraAtual = (int)($provento['carteira_id'] ?? $carteiraSelecionada ?? 0); ?>
        <select name="carteira_id" required class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
          <option value="">Selecione a carteira...</option>
          <?php foreach ($wallets as $w): ?>
            <option value="<?= (int)$w['id'] ?>" <?= $carteiraAtual === (int)$w['id'] ? 'selected' : '' ?>><?= e($w['nome']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tipo</label>
          <?php $tipoAtual = $provento['tipo'] ?? 'DIVIDENDO'; ?>
          <select name="tipo" id="select-provento-tipo" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
            <option value="DIVIDENDO" <?= $tipoAtual === 'DIVIDENDO' ? 'selected' : '' ?>>Dividendo</option>
            <option value="JCP" <?= $tipoAtual === 'JCP' ? 'selected' : '' ?>>JCP</option>
            <option value="RENDIMENTO" <?= $tipoAtual === 'RENDIMENTO' ? 'selected' : '' ?>>Rendimento</option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Data de Pagamento</label>
          <input type="date" name="data_pagamento" required
            value="<?= e($provento['data_pagamento'] ?? date('Y-m-d')) ?>"
            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Valor Recebido (R$)</label>
        <input type="number" step="0.01" min="0.01" name="valor" required placeholder="0,00"
          value="<?= e((string)($provento['valor'] ?? '')) ?>"
          class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Observação</label>
        <input type="text" name="observacao" placeholder="Observação opcional..."
          value="<?= e($provento['observacao'] ?? '') ?>"
          class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
      </div>
    </div>
    <div class="flex items-center gap-3 pt-5 mt-6 border-t border-slate-100">
      <button type="submit" class="btn-primary inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider px-5 py-2.5">
        <i class="ph ph-check text-sm"></i> Salvar Provento
      </button>
      <a href="<?= BASE_URL ?>/proventos.php" class="btn-secondary inline-flex items-center gap-2 text-xs font-semibold px-4 py-2.5">
        Cancelar
      </a>
    </div>
  </form>
</div>

<script>
(function() {
  const selectAtivo = document.querySelector('select[name="ativo_id"]');
  const selectTipo = document.getElementById('select-provento-tipo');

  function ajustarTipoAutomatico() {
    if (!selectAtivo || !selectTipo) return;
    const selectedOpt = selectAtivo.options[selectAtivo.selectedIndex];
    if (!selectedOpt) return;
    const ativoTipo = selectedOpt.dataset.tipo;

    if (ativoTipo === 'FII') {
      selectTipo.value = 'RENDIMENTO';
    } else if (ativoTipo === 'ACAO' && selectTipo.value === 'RENDIMENTO') {
      selectTipo.value = 'DIVIDENDO';
    }
  }

  selectAtivo?.addEventListener('change', ajustarTipoAutomatico);
})();
</script>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>
