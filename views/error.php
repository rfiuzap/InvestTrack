<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Erro · InvestTrack</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 flex items-center justify-center min-h-screen">
<div class="max-w-lg text-center px-6">
  <p class="text-5xl font-bold text-rose-600">Erro</p>
  <p class="mt-2 text-zinc-500"><?= e($errorMessage ?? 'Ocorreu um erro inesperado.') ?></p>
  <a href="<?= BASE_URL ?>/index.php" class="mt-4 inline-block text-sm font-medium text-zinc-900 underline">Voltar ao início</a>
</div>
</body>
</html>
