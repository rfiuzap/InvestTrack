<?php require BASE_PATH . '/views/layout/header.php'; ?>

<div class="max-w-xl bg-white border border-slate-200/90 rounded-2xl shadow-sm p-7">
  <div class="mb-6 pb-4 border-b border-slate-100">
    <h2 class="text-base font-extrabold text-slate-900 tracking-tight"><?= $ativo ? 'Editar Ativo' : 'Novo Ativo' ?></h2>
    <p class="text-xs text-slate-500 font-medium mt-0.5"><?= $ativo ? 'Atualize os dados e parâmetros do ativo.' : 'Preencha os dados do ativo para monitoramento e carteira.' ?></p>
  </div>
  <form method="POST">
    <?= csrf_field() ?>
    <div class="space-y-4">
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Ticker</label>
        <input type="text" id="input-ticker" name="ticker" maxlength="8" required placeholder="Ex: PETR4"
          value="<?= e($ativo['ticker'] ?? '') ?>" autocomplete="off"
          class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-bold text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none uppercase transition-all shadow-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nome do Ativo</label>
        <div class="relative">
          <input type="text" id="input-nome" name="nome" required placeholder="<?= $ativo ? 'Ex: Petrobras PN' : 'Preenchido automaticamente a partir do ticker' ?>"
            value="<?= e($ativo['nome'] ?? '') ?>" <?= $ativo ? '' : 'readonly' ?>
            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none read-only:bg-slate-50/80 read-only:text-slate-500 read-only:cursor-not-allowed transition-all shadow-sm">
          <i id="nome-spinner" class="ph ph-spinner animate-spin absolute right-3.5 top-3 text-blue-600 hidden"></i>
        </div>
        <p id="nome-status" class="mt-1 text-xs text-slate-500 font-medium"></p>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Tipo</label>
          <?php $tipoAtual = $ativo['tipo'] ?? 'ACAO'; ?>
          <select name="tipo" class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
            <?php foreach (['ACAO' => 'Ação', 'FII' => 'Fundo Imobiliário', 'ETF' => 'ETF', 'BDR' => 'BDR', 'OUTRO' => 'Outro'] as $val => $label): ?>
              <option value="<?= $val ?>" <?= $tipoAtual === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Setor</label>
          <input type="text" name="setor" placeholder="Ex: Petróleo e Gás"
            value="<?= e($ativo['setor'] ?? '') ?>"
            class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Cotação Manual (opcional)</label>
        <input type="number" step="0.01" min="0" name="cotacao_atual" placeholder="0,00"
          value="<?= isset($ativo['cotacao_atual']) ? e((string)$ativo['cotacao_atual']) : '' ?>"
          class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 focus:border-blue-600 focus:ring-2 focus:ring-blue-100 outline-none transition-all shadow-sm">
        <p class="mt-1 text-xs text-slate-400 font-medium">Usada como fallback caso a atualização automática via API não esteja disponível.</p>
      </div>
    </div>
    <div class="flex items-center gap-3 pt-5 mt-6 border-t border-slate-100">
      <button type="submit" class="btn-primary inline-flex items-center gap-2 text-xs font-bold uppercase tracking-wider px-5 py-2.5">
        <i class="ph ph-check text-sm"></i> Salvar Ativo
      </button>
      <a href="<?= BASE_URL ?>/ativos.php" class="btn-secondary inline-flex items-center gap-2 text-xs font-semibold px-4 py-2.5">
        Cancelar
      </a>
    </div>
  </form>
</div>

<?php if (!$ativo): ?>
<script>
(function () {
  const tickerInput = document.getElementById('input-ticker');
  const nomeInput = document.getElementById('input-nome');
  const cotacaoInput = document.querySelector('[name="cotacao_atual"]');
  const spinner = document.getElementById('nome-spinner');
  const status = document.getElementById('nome-status');
  let timer = null;

  function resetNome(mensagem) {
    nomeInput.readOnly = false;
    nomeInput.value = '';
    status.textContent = mensagem || '';
    status.className = 'mt-1 text-xs text-zinc-500';
  }

  tickerInput.addEventListener('input', () => {
    const ticker = tickerInput.value.trim().toUpperCase();
    clearTimeout(timer);

    if (ticker.length < 4) {
      resetNome('');
      return;
    }

    timer = setTimeout(async () => {
      spinner.classList.remove('hidden');
      status.textContent = 'Buscando nome do ativo...';
      status.className = 'mt-1 text-xs text-zinc-500';

      try {
        const res = await fetch(`<?= BASE_URL ?>/api/asset_info.php?ticker=${encodeURIComponent(ticker)}`);
        const data = await res.json();

        if (data.success && data.nome) {
          nomeInput.value = data.nome;
          nomeInput.readOnly = true;
          if (cotacaoInput && !cotacaoInput.value && data.preco) {
            cotacaoInput.value = data.preco;
          }
          status.textContent = 'Nome identificado automaticamente.';
          status.className = 'mt-1 text-xs text-emerald-600';
        } else {
          resetNome('Não foi possível identificar o ativo. Preencha o nome manualmente.');
        }
      } catch (err) {
        resetNome('Falha ao consultar a cotação. Preencha o nome manualmente.');
      } finally {
        spinner.classList.add('hidden');
      }
    }, 500);
  });
})();
</script>
<?php endif; ?>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>

