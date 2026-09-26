<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$action = $_GET['action'] ?? 'index';

try {
    match ($action) {
        'create' => TransactionController::create($db),
        'edit' => TransactionController::edit($db),
        'delete' => TransactionController::delete($db),
        'import' => TransactionController::import($db),
        'template_csv' => TransactionController::templateCsv(),
        default => TransactionController::index($db),
    };
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = $e->getMessage();
    require BASE_PATH . '/views/error.php';
}
