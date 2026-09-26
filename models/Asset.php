<?php
declare(strict_types=1);

class Asset
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(): array
    {
        return $this->db->query('SELECT * FROM ativos ORDER BY ticker ASC')->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ativos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByTicker(string $ticker): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM ativos WHERE ticker = :ticker');
        $stmt->execute(['ticker' => strtoupper(trim($ticker))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO ativos (ticker, nome, tipo, setor) VALUES (:ticker, :nome, :tipo, :setor)'
        );
        $stmt->execute([
            'ticker' => strtoupper(trim($data['ticker'])),
            'nome' => trim($data['nome']),
            'tipo' => $data['tipo'],
            'setor' => ($data['setor'] ?? '') !== '' ? $data['setor'] : null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ativos SET ticker = :ticker, nome = :nome, tipo = :tipo, setor = :setor WHERE id = :id'
        );
        return $stmt->execute([
            'ticker' => strtoupper(trim($data['ticker'])),
            'nome' => trim($data['nome']),
            'tipo' => $data['tipo'],
            'setor' => ($data['setor'] ?? '') !== '' ? $data['setor'] : null,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM ativos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }

    public function updateQuote(int $id, float $preco): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE ativos SET cotacao_atual = :preco, cotacao_atualizada_em = NOW() WHERE id = :id'
        );
        return $stmt->execute(['preco' => $preco, 'id' => $id]);
    }

    public function hasTransactions(int $id): bool
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) FROM transacoes WHERE ativo_id = :id');
        $stmt->execute(['id' => $id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
