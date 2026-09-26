<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

spl_autoload_register(function (string $class): void {
    $path = BASE_PATH . '/models/' . $class . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

header('Content-Type: application/json; charset=utf-8');

$db = Database::getConnection();
$transactionModel = new Transaction($db);

try {
    $ativoId = isset($_GET['ativo_id']) ? (int)$_GET['ativo_id'] : null;
    $carteiraId = resolve_carteira_id();
    $transacoes = $ativoId ? $transactionModel->allByAsset($ativoId, $carteiraId) : $transactionModel->all($carteiraId);
    echo json_encode(['success' => true, 'transacoes' => $transacoes]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
