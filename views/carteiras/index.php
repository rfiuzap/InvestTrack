<?php require BASE_PATH . '/views/layout/header.php'; ?>
<?php $wallets = $wallets ?? []; ?>

<div class="flex flex-wrap items-end justify-between gap-3 mb-5">
  <div>
    <h2 class="text-base font-extrabold text-slate-900 tracking-tight">Gerenciar carteiras</h2>
    <p class="text-xs text-slate-500 font-medium mt-1">Crie novos nomes e inative carteiras sem apagar o histórico.</p>
  </div>
</div>

<?php if (($_SESSION['investtrack_database'] ?? null) === 'banco_producao'): ?>
  <div class="mb-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
    Este ambiente de demonstração aceita alterações temporárias. A cada 2 horas, no próximo acesso, as carteiras e dados voltam ao padrão João e Maria.
  </div>
<?php endif; ?>

<section class="mb-6 max-w-xl rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm">
  <div class="mb-4">
    <h3 class="text-sm font-extrabold text-slate-900">Criar nova carteira</h3>
    <p class="mt-1 text-xs font-medium text-slate-500">O nome ficará disponível para novas operações.</p>
  </div>
  <form method="POST" action="<?= BASE_URL ?>/carteiras.php?action=create" class="flex flex-wrap items-end gap-3">
    <?= csrf_field() ?>
    <div class="min-w-60 flex-1">
      <label for="nome-carteira" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-slate-700">Nome</label>
      <input id="nome-carteira" name="nome" type="text" maxlength="80" required placeholder="Ex: Família"
        class="w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-sm font-medium text-slate-900 outline-none transition-all focus:border-blue-600 focus:ring-2 focus:ring-blue-100">
    </div>
    <button type="submit" class="btn-primary inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold">
      <i class="ph ph-plus text-sm"></i> Criar carteira
    </button>
  </form>
</section>

<section class="tabela-card">
  <div class="border-b border-slate-200 px-4 py-3">
    <h3 class="text-sm font-extrabold text-slate-900">Carteiras cadastradas</h3>
  </div>
  <div class="overflow-x-auto">
    <table class="w-full text-sm">
      <thead>
        <tr class="border-b border-slate-200 bg-slate-50/80 text-left text-[11px] font-bold uppercase tracking-wider text-slate-500">
          <th class="px-4 py-3">Nome</th>
          <th class="px-4 py-3">Status</th>
          <th class="px-4 py-3 text-right">Ações</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        <?php foreach ($wallets as $wallet): $ativa = (int)$wallet['ativa'] === 1; ?>
          <tr class="<?= $ativa ? 'hover:bg-slate-50/80' : 'bg-slate-50/60 text-slate-400' ?>">
            <td class="px-4 py-3.5 font-bold <?= $ativa ? 'text-slate-900' : 'text-slate-500' ?>"><?= e($wallet['nome']) ?></td>
            <td class="px-4 py-3.5">
              <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-bold <?= $ativa ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-slate-200 bg-slate-100 text-slate-500' ?>">
                <span class="h-1.5 w-1.5 rounded-full <?= $ativa ? 'bg-emerald-500' : 'bg-slate-400' ?>"></span>
                <?= $ativa ? 'Ativa' : 'Inativa' ?>
              </span>
            </td>
            <td class="px-4 py-3.5 text-right">
              <?php if ($ativa): ?>
                <form method="POST" action="<?= BASE_URL ?>/carteiras.php?action=deactivate" class="inline" onsubmit="return confirm('Inativar esta carteira? O histórico será preservado.');">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int)$wallet['id'] ?>">
                  <button type="submit" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-amber-50 hover:text-amber-700" title="Inativar carteira">
                    <i class="ph ph-pause-circle text-lg"></i>
                  </button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</section>

<?php require BASE_PATH . '/views/layout/footer.php'; ?>