<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

spl_autoload_register(function (string $class): void {
    foreach ([BASE_PATH . '/models/', BASE_PATH . '/services/'] as $dir) {
        $path = $dir . $class . '.php';
        if (is_file($path)) {
            require_once $path;
            return;
        }
    }
});

header('Content-Type: application/json; charset=utf-8');

$db = Database::getConnection();
$portfolioService = new PortfolioService($db);
$carteiraId = resolve_carteira_id();

try {
    echo json_encode([
        'success' => true,
        'resumo' => $portfolioService->resumoGeral($carteiraId),
        'alocacao' => $portfolioService->alocacaoPorAtivo($carteiraId),
        'evolucao' => $portfolioService->evolucaoMensal($carteiraId),
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
