<?php
declare(strict_types=1);

require_once __DIR__ . '/config/app.php';

$_SESSION['investtrack_database'] = 'banco_producao';

$page = $_GET['page'] ?? null;
if ($page !== null) {
    $allowed = ['dashboard', 'carteiras', 'trading', 'ativos', 'operacoes', 'proventos', 'relatorios'];
    if (in_array($page, $allowed, true)) {
        $params = $_GET;
        unset($params['page']);
        $queryString = !empty($params) ? '?' . http_build_query($params) : '';
        header('Location: ' . BASE_URL . '/' . $page . '.php' . $queryString);
        exit;
    }
}

header('Location: ' . BASE_URL . '/dashboard.php');
exit;
