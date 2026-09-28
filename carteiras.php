<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$action = $_GET['action'] ?? 'index';

try {
    match ($action) {
        'create' => WalletController::store($db),
        'deactivate' => WalletController::deactivate($db),
        default => WalletController::index($db),
    };
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = $e->getMessage();
    require BASE_PATH . '/views/error.php';
}