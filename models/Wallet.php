<?php
declare(strict_types=1);

/**
 * Representa uma carteira (dono das operações): Renato, Vicente, etc.
 * Não é autenticação — apenas um filtro de qual pessoa é dona da operação.
 */
class Wallet
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM carteiras WHERE ativa = 1 ORDER BY nome ASC')->fetchAll();
    }

    public function allIncludingInactive(): array
    {
        return $this->db->query('SELECT * FROM carteiras ORDER BY ativa DESC, nome ASC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM carteiras WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(string $nome): int
    {
        $stmt = $this->db->prepare('INSERT INTO carteiras (nome, ativa) VALUES (:nome, 1)');
        $stmt->execute(['nome' => $nome]);
        return (int)$this->db->lastInsertId();
    }

    public function deactivate(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE carteiras SET ativa = 0 WHERE id = :id AND ativa = 1');
        $stmt->execute(['id' => $id]);
    }
}
