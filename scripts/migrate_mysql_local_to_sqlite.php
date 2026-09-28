<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/app.php';

$_SESSION['investtrack_database'] = 'banco_local';
require_once dirname(__DIR__) . '/bootstrap.php';

$mysqlHost = getenv('INVESTTRACK_MYSQL_HOST') ?: 'localhost';
$mysqlName = getenv('INVESTTRACK_MYSQL_DATABASE') ?: 'banco_local';
$mysqlUser = getenv('INVESTTRACK_MYSQL_USER') ?: 'root';
$mysqlPassword = getenv('INVESTTRACK_MYSQL_PASSWORD') ?: '';
$mysqlPort = getenv('INVESTTRACK_MYSQL_PORT');
$mysqlDsn = "mysql:host={$mysqlHost};dbname={$mysqlName};charset=utf8mb4";
if ($mysqlPort !== false && $mysqlPort !== '') {
    $mysqlDsn .= ";port={$mysqlPort}";
}

try {
    $source = new PDO($mysqlDsn, $mysqlUser, $mysqlPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    foreach (['ativos', 'transacoes', 'proventos'] as $table) {
        $count = (int)$db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        if ($count !== 0) {
            throw new RuntimeException("A tabela SQLite {$table} ja possui dados; importacao cancelada.");
        }
    }

    $sourceWallets = $source->query('SELECT id, nome FROM carteiras ORDER BY id')->fetchAll();
    $targetWallets = $db->query('SELECT id, nome FROM carteiras ORDER BY id')->fetchAll();
    if ($sourceWallets !== $targetWallets) {
        throw new RuntimeException('As carteiras SQLite diferem das carteiras MySQL; importacao cancelada para preservar os relacionamentos.');
    }

    $db->beginTransaction();
    $imported = [];
    foreach (['ativos', 'transacoes', 'proventos'] as $table) {
        $rows = $source->query("SELECT * FROM {$table} ORDER BY id")->fetchAll();
        $count = 0;
        foreach ($rows as $row) {
            $columns = array_keys($row);
            $quotedColumns = array_map(static fn(string $column): string => '"' . $column . '"', $columns);
            $placeholders = implode(', ', array_fill(0, count($columns), '?'));
            $statement = $db->prepare(
                "INSERT INTO \"{$table}\" (" . implode(', ', $quotedColumns) . ") VALUES ({$placeholders})"
            );
            $statement->execute(array_values($row));
            $count++;
        }
        $imported[$table] = $count;
    }
    $db->commit();

    foreach ($imported as $table => $count) {
        printf("%s: %d registros importados\n", $table, $count);
    }
    echo "Migracao local concluida.\n";
} catch (Throwable $exception) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    fwrite(STDERR, 'Migracao cancelada: ' . $exception->getMessage() . PHP_EOL);
    exit(1);
}