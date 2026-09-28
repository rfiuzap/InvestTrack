<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            // Na demo publicada só existe o banco de demonstração, guardado em data/ (bloqueada pelo .htaccess).
            $isDemo = APP_ENV === 'demo';
            $databaseName = $isDemo || ($_SESSION['investtrack_database'] ?? null) === 'banco_producao'
                ? 'banco_producao'
                : 'banco_local';
            $dataDirectory = $isDemo ? BASE_PATH . DIRECTORY_SEPARATOR . 'data' : getenv('INVESTTRACK_SQLITE_DIR');
            if (($dataDirectory === false || trim($dataDirectory) === '') && isset($_SERVER['INVESTTRACK_SQLITE_DIR'])) {
                $dataDirectory = (string)$_SERVER['INVESTTRACK_SQLITE_DIR'];
            }
            if ($dataDirectory === false || trim($dataDirectory) === '') {
                $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
                $dataDirectory = $documentRoot !== false
                    ? dirname($documentRoot) . DIRECTORY_SEPARATOR . 'investtrack-data'
                    : BASE_PATH . DIRECTORY_SEPARATOR . 'data';
            }
            $sqlitePath = $dataDirectory . DIRECTORY_SEPARATOR . $databaseName . '.sqlite';

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            try {
                if (!is_dir($dataDirectory) && !mkdir($dataDirectory, 0770, true) && !is_dir($dataDirectory)) {
                    throw new RuntimeException('Não foi possível criar o diretório do banco SQLite: ' . $dataDirectory);
                }

                $pdo = new PDO('sqlite:' . $sqlitePath, null, null, $options);
                $pdo->exec('PRAGMA foreign_keys = ON');
                $pdo->exec('PRAGMA busy_timeout = 5000');
                $schema = file_get_contents(BASE_PATH . '/database/schema.sqlite.sql');
                if ($schema === false) {
                    throw new RuntimeException('Não foi possível ler o esquema SQLite.');
                }
                $pdo->exec($schema);

                $walletColumns = $pdo->query('PRAGMA table_info(carteiras)')->fetchAll(PDO::FETCH_COLUMN, 1);
                if (!in_array('ativa', $walletColumns, true)) {
                    $pdo->exec('ALTER TABLE carteiras ADD COLUMN ativa INTEGER NOT NULL DEFAULT 1');
                }

                if ($databaseName === 'banco_producao') {
                    $resetInterval = 2 * 60 * 60;
                    $pdo->beginTransaction();
                    try {
                        $lastReset = $pdo->query('SELECT reiniciado_em FROM demo_controle WHERE id = 1')->fetchColumn();
                        $assetCount = (int)$pdo->query('SELECT COUNT(*) FROM ativos')->fetchColumn();
                        $shouldReset = $assetCount === 0
                            || $lastReset === false
                            || (time() - (int)$lastReset) >= $resetInterval;

                        if ($shouldReset) {
                            $pdo->exec('DELETE FROM proventos');
                            $pdo->exec('DELETE FROM transacoes');
                            $pdo->exec('DELETE FROM ativos');
                            $pdo->exec('DELETE FROM carteiras');

                            $demoSeedPath = BASE_PATH . '/database/banco_producao.sql';
                            if (!is_readable($demoSeedPath)) {
                                throw new RuntimeException('Seed demonstrativo ausente ou sem permissão de leitura: ' . $demoSeedPath);
                            }
                            $demoData = file_get_contents($demoSeedPath);
                            if ($demoData === false) {
                                throw new RuntimeException('Não foi possível ler o seed demonstrativo: ' . $demoSeedPath);
                            }
                            $pdo->exec($demoData);
                            $stmt = $pdo->prepare('INSERT OR REPLACE INTO demo_controle (id, reiniciado_em) VALUES (1, :reiniciado_em)');
                            $stmt->execute(['reiniciado_em' => time()]);
                        }
                        $pdo->commit();
                    } catch (Throwable $ex) {
                        if ($pdo->inTransaction()) {
                            $pdo->rollBack();
                        }
                        throw $ex;
                    }
                } else {
                    $pdo->exec("INSERT OR IGNORE INTO carteiras (nome) VALUES ('Renato'), ('Vicente')");
                }

                self::$instance = $pdo;
            } catch (Throwable $ex) {
                die('Erro de conexão com o banco SQLite: ' . $ex->getMessage());
            }
        }
        return self::$instance;
    }
}
