<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$action = $_GET['action'] ?? (!empty($_GET['ticker']) ? 'index' : 'cards');

try {
    match ($action) {
        'index' => TradingController::index($db),
        'cards' => TradingController::cards($db),
        default => TradingController::cards($db),
    };
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = $e->getMessage();
    require BASE_PATH . '/views/error.php';
}
