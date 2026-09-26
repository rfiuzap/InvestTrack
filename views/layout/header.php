<?php /** @var string $pageTitle */ ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/assets/favicon.svg">
<title><?= e($pageTitle ?? 'InvestTrack') ?> · InvestTrack</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://unpkg.com/@phosphor-icons/web"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'BlinkMacSystemFont', '"Segoe UI"', 'Roboto', 'sans-serif'],
        },
        colors: {
          primary: {
            DEFAULT: '#1d4ed8',
            hover: '#1e40af',
            light: '#eff6ff',
          },
          ink: {
            DEFAULT: '#0f172a',
            light: '#334155',
          },
          muted: '#64748b',
          line: '#e2e8f0',
          paper: '#ffffff',
          wash: '#f8fafc',
          teal: {
            DEFAULT: '#0d9488',
            dark: '#0f766e',
            light: '#f0fdfa',
          },
        },
        boxShadow: {
          sm: '0 1px 2px 0 rgba(0, 0, 0, 0.05)',
          DEFAULT: '0 4px 12px -2px rgba(15, 23, 42, 0.06), 0 2px 6px -2px rgba(15, 23, 42, 0.04)',
          md: '0 10px 20px -3px rgba(15, 23, 42, 0.08), 0 4px 8px -4px rgba(15, 23, 42, 0.04)',
          lg: '0 20px 25px -5px rgba(15, 23, 42, 0.1), 0 8px 10px -6px rgba(15, 23, 42, 0.06)',
          glow: '0 4px 14px rgba(29, 78, 216, 0.25)',
        },
        borderRadius: {
          DEFAULT: '12px',
          sm: '8px',
          lg: '16px',
        }
      },
    },
  };
</script>
</head>
<body class="text-slate-900 font-sans antialiased min-h-screen">
<div class="min-h-screen flex">
<?php require BASE_PATH . '/views/layout/sidebar.php'; ?>
<div class="flex-1 flex flex-col min-w-0">
<header class="bg-white/80 backdrop-blur-md border-b border-slate-200/80 px-6 py-4 flex items-center justify-between sticky top-0 z-20 transition-all">
  <div>
    <h1 class="text-lg font-bold text-slate-900 tracking-tight"><?= e($pageTitle ?? '') ?></h1>
  </div>
  <div class="flex items-center gap-3">
    <?php
      $cartHeaderId = resolve_carteira_id();
      if ($cartHeaderId !== null && !empty($sidebarWallets)):
        $nomeCartHeader = '';
        foreach ($sidebarWallets as $sw) {
          if ((int)$sw['id'] === $cartHeaderId) { $nomeCartHeader = $sw['nome']; break; }
        }
        if ($nomeCartHeader !== ''):
    ?>
      <div class="hidden sm:inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-semibold <?= carteira_badge_class($nomeCartHeader) ?>">
        <i class="ph ph-wallet text-xs"></i>
        <span>Carteira: <?= e($nomeCartHeader) ?></span>
      </div>
    <?php endif; endif; ?>
    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
      <i class="ph ph-clock text-base text-slate-400"></i>
      <span id="current-date" class="tracking-wide"></span>
    </div>
  </div>
</header>
<main class="flex-1 p-6">
<?php if ($msg = flash('sucesso')): ?>
  <div class="mb-5 flex items-center justify-between gap-2.5 rounded-xl border border-emerald-200 bg-emerald-50/90 px-4 py-3 text-sm font-medium text-emerald-800 shadow-sm animate-in fade-in flash-alert transition-opacity duration-300">
    <div class="flex items-center gap-2.5">
      <i class="ph ph-check-circle text-lg text-emerald-600 shrink-0"></i>
      <span><?= e($msg) ?></span>
    </div>
    <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-emerald-600/70 hover:text-emerald-900 transition-colors p-1" title="Fechar">
      <i class="ph ph-x text-base"></i>
    </button>
  </div>
<?php endif; ?>
<?php if ($msg = flash('erro')): ?>
  <div class="mb-5 flex items-center justify-between gap-2.5 rounded-xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm font-medium text-rose-800 shadow-sm animate-in fade-in flash-alert transition-opacity duration-300">
    <div class="flex items-center gap-2.5">
      <i class="ph ph-warning-circle text-lg text-rose-600 shrink-0"></i>
      <span><?= e($msg) ?></span>
    </div>
    <button type="button" onclick="this.closest('.flash-alert').remove()" class="text-rose-600/70 hover:text-rose-900 transition-colors p-1" title="Fechar">
      <i class="ph ph-x text-base"></i>
    </button>
  </div>
<?php endif; ?>
