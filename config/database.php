<?php
declare(strict_types=1);

final class Database
{
    private static ?PDO $instance = null;

    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $host = 'localhost';
            $dbname = 'banco_producao';
            $user = 'root';
            $pass = '';
            $charset = 'utf8mb4';
            $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $ex) {
                die('Erro de conexão com o banco de dados: ' . $ex->getMessage());
            }
        }
        return self::$instance;
    }
}
