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
$quoteService = new QuoteService();

try {
    if (($_GET['action'] ?? '') === 'refresh_all') {
        $resultado = $quoteService->atualizarTodasCotacoes($db);
        echo json_encode(['success' => true, 'cotacoes' => $resultado]);
        exit;
    }

    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
        throw new InvalidArgumentException('Ativo inválido.');
    }

    $ativo = (new Asset($db))->find($id);
    if (!$ativo) {
        throw new InvalidArgumentException('Ativo não encontrado.');
    }

    $preco = $quoteService->atualizarCotacaoAtivo($db, $id, $ativo['ticker']);

    echo json_encode([
        'success' => $preco !== null,
        'preco' => $preco,
        'message' => $preco === null ? 'Não foi possível obter a cotação. Atualize manualmente.' : null,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
