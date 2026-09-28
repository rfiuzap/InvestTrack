<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

// Na demo publicada não existe "meus dados": sempre volta para a demonstração.
if (APP_ENV === 'demo') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$_SESSION['investtrack_database'] = 'banco_local';

header('Location: ' . BASE_URL . '/dashboard.php');
exit;
