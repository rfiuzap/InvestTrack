<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';

spl_autoload_register(function (string $class): void {
    $path = BASE_PATH . '/services/' . $class . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

header('Content-Type: application/json; charset=utf-8');

try {
    $ticker = strtoupper(trim($_GET['ticker'] ?? ''));
    if (!preg_match('/^[A-Z0-9]{4,8}$/', $ticker)) {
        throw new InvalidArgumentException('Ticker inválido.');
    }

    $info = (new QuoteService())->buscarInfo($ticker);

    echo json_encode([
        'success' => $info !== null,
        'nome' => $info['nome'] ?? null,
        'preco' => $info['preco'] ?? null,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
