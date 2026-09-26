<?php
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');
if ($currentScript === '' || $currentScript === 'index.php') {
    $currentScript = 'dashboard.php';
}
$currentAction = $_GET['action'] ?? '';
$actionQuery = !empty($currentAction) ? '&action=' . urlencode((string)$currentAction) : '';

if (!isset($sidebarWallets)) {
    global $db;
    if ($db instanceof PDO) {
        $sidebarWallets = (new Wallet($db))->all();
    } else {
        $sidebarWallets = [];
    }
}
$carteiraAtivaGlobal = resolve_carteira_id();
?>
<aside class="w-64 shrink-0 bg-white border-r border-slate-200 flex flex-col shadow-sm">
  <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
    <div class="h-9 w-9 rounded-xl bg-gradient-to-br from-blue-700 to-blue-500 flex items-center justify-center text-white shadow-[0_4px_14px_rgba(29,78,216,0.3)] transition-transform duration-200 hover:scale-105">
      <i class="ph ph-chart-line-up text-lg"></i>
    </div>
    <div>
      <span class="font-extrabold text-slate-900 text-base tracking-tight block leading-tight">InvestTrack</span>
      <span class="text-[11px] font-semibold text-slate-400 tracking-wider uppercase">Finance Pro</span>
      <span class="text-[10px] font-medium text-slate-400 tracking-wide block">v<?= e(defined('APP_VERSION') ? APP_VERSION : '0.0.0') ?></span>
    </div>
  </div>

  <!-- Filtro Global de Carteira (Afeta todas as páginas) -->
  <div class="px-3.5 py-4 border-b border-slate-100 bg-slate-50/70">
    <div class="flex items-center justify-between px-1 mb-2">
      <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
        <i class="ph ph-wallet text-xs text-blue-600"></i> Carteira Ativa
      </span>
      <?php if ($carteiraAtivaGlobal !== null): ?>
        <a href="<?= BASE_URL ?>/<?= $currentScript ?>?carteira=todos<?= $actionQuery ?>" class="text-[10px] font-bold text-blue-600 hover:text-blue-800 transition-colors" title="Ver consolidado de todas as carteiras">
          Todas
        </a>
      <?php endif; ?>
    </div>

    <div class="flex flex-col gap-1">
      <!-- Todas as Carteiras -->
      <a href="<?= BASE_URL ?>/<?= $currentScript ?>?carteira=todos<?= $actionQuery ?>"
        class="flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold transition-all duration-150 <?= $carteiraAtivaGlobal === null ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_4px_12px_rgba(29,78,216,0.22)]' : 'text-slate-600 hover:bg-white hover:text-slate-900 border border-transparent hover:border-slate-200/80' ?>">
        <div class="flex items-center gap-2">
          <i class="ph ph-squares-four text-sm <?= $carteiraAtivaGlobal === null ? 'text-white' : 'text-slate-400' ?>"></i>
          <span>Todas as Carteiras</span>
        </div>
        <?php if ($carteiraAtivaGlobal === null): ?>
          <i class="ph ph-check text-xs font-bold"></i>
        <?php endif; ?>
      </a>

      <!-- Carteiras Cadastradas (Renato, Vicente, etc.) -->
      <?php foreach ($sidebarWallets as $w): 
        $isAtiva = ($carteiraAtivaGlobal === (int)$w['id']);
        $wNome = strtolower(trim($w['nome']));
        $isRenato = str_contains($wNome, 'renato');
        $isVicente = str_contains($wNome, 'vicente');

        if ($isRenato) {
          $corDot = 'bg-purple-600';
          $corAtiva = 'bg-gradient-to-r from-purple-700 via-purple-600 to-indigo-600 text-white shadow-[0_4px_14px_rgba(126,34,206,0.32)]';
          $corInativa = 'text-slate-600 hover:bg-purple-50/70 hover:text-purple-900 border border-transparent hover:border-purple-200/80';
        } elseif ($isVicente) {
          $corDot = 'bg-emerald-500';
          $corAtiva = 'bg-gradient-to-r from-emerald-600 via-teal-600 to-teal-700 text-white shadow-[0_4px_14px_rgba(13,148,136,0.32)]';
          $corInativa = 'text-slate-600 hover:bg-emerald-50/70 hover:text-emerald-900 border border-transparent hover:border-emerald-200/80';
        } else {
          $corDot = 'bg-blue-500';
          $corAtiva = 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_4px_12px_rgba(29,78,216,0.22)]';
          $corInativa = 'text-slate-600 hover:bg-white hover:text-slate-900 border border-transparent hover:border-slate-200/80';
        }
      ?>
      <a href="<?= BASE_URL ?>/<?= $currentScript ?>?carteira=<?= (int)$w['id'] ?><?= $actionQuery ?>"
        class="flex items-center justify-between rounded-xl px-2.5 py-2 text-xs font-bold transition-all duration-150 <?= $isAtiva ? $corAtiva : $corInativa ?>">
        <div class="flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full <?= $isAtiva ? 'bg-white shadow-sm ring-2 ring-white/30' : $corDot ?>"></span>
          <span><?= e($w['nome']) ?></span>
        </div>
        <?php if ($isAtiva): ?>
          <i class="ph ph-check text-xs font-bold"></i>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>

  <nav class="flex-1 px-3 py-4 space-y-1">
    <?php
      $items = [
        ['file' => 'dashboard.php', 'label' => 'Dashboard', 'icon' => 'ph-squares-four'],
        ['file' => 'trading.php', 'action' => 'cards', 'label' => 'Painel de Trading', 'icon' => 'ph-grid-four'],
        ['file' => 'ativos.php', 'label' => 'Ativos', 'icon' => 'ph-stack'],
        ['file' => 'operacoes.php', 'label' => 'Operações', 'icon' => 'ph-arrows-left-right'],
        ['file' => 'proventos.php', 'label' => 'Proventos', 'icon' => 'ph-coins'],
        ['file' => 'relatorios.php', 'label' => 'Relatórios', 'icon' => 'ph-file-text'],
      ];
      foreach ($items as $item):
        $active = ($currentScript === $item['file']);
    ?>
    <a href="<?= BASE_URL ?>/<?= $item['file'] ?><?= !empty($item['action']) ? '?action=' . $item['action'] : '' ?>"
       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-semibold transition-all duration-150 <?= $active ? 'bg-gradient-to-r from-blue-700 to-blue-600 text-white shadow-[0_4px_12px_rgba(29,78,216,0.25)]' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' ?>">
      <i class="ph <?= $item['icon'] ?> text-base"></i>
      <?= e($item['label']) ?>
    </a>
    <?php endforeach; ?>
  </nav>
  <div class="px-6 py-4 border-t border-slate-100 text-xs text-slate-400 flex items-center justify-between">
    <span>InvestTrack · v2.0</span>
    <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse" title="Sistema online"></span>
  </div>
</aside>
