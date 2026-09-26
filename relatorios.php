<?php
declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

try {
    PortfolioController::relatorios($db);
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = $e->getMessage();
    require BASE_PATH . '/views/error.php';
}
