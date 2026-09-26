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

try {
    $ativoId = (int)($_GET['ativo_id'] ?? 0);
    if ($ativoId <= 0) {
        throw new InvalidArgumentException('Ativo inválido.');
    }

    $ativo = (new Asset($db))->find($ativoId);
    if (!$ativo) {
        throw new InvalidArgumentException('Ativo não encontrado.');
    }

    $carteiraId = isset($_GET['carteira_id']) && $_GET['carteira_id'] !== '' ? (int)$_GET['carteira_id'] : null;
    $excludeId = isset($_GET['exclude_id']) && $_GET['exclude_id'] !== '' ? (int)$_GET['exclude_id'] : null;

    $posicao = (new PortfolioService($db))->calcularPosicaoAtivo($ativo, $carteiraId, $excludeId);

    echo json_encode([
        'success' => true,
        'quantidade' => $posicao['quantidade'],
        'ticker' => $ativo['ticker'],
        'nome' => $ativo['nome'],
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
