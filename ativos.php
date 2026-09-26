<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$action = $_GET['action'] ?? 'index';

try {
    match ($action) {
        'create' => AssetController::create($db),
        'edit' => AssetController::edit($db),
        'delete' => AssetController::delete($db),
        default => AssetController::index($db),
    };
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = $e->getMessage();
    require BASE_PATH . '/views/error.php';
}
