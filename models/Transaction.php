<?php
declare(strict_types=1);

class Transaction
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(?int $carteiraId = null): array
    {
        $sql = 'SELECT t.*, a.ticker, a.nome, a.tipo AS ativo_tipo, c.nome AS carteira_nome FROM transacoes t
                JOIN ativos a ON a.id = t.ativo_id
                JOIN carteiras c ON c.id = t.carteira_id';
        $params = [];
        if ($carteiraId !== null) {
            $sql .= ' WHERE t.carteira_id = :carteira_id';
            $params['carteira_id'] = $carteiraId;
        }
        $sql .= ' ORDER BY t.data_operacao DESC, t.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function allByAsset(int $ativoId, ?int $carteiraId = null, ?int $excludeTransactionId = null): array
    {
        $sql = 'SELECT * FROM transacoes WHERE ativo_id = :id';
        $params = ['id' => $ativoId];
        if ($carteiraId !== null) {
            $sql .= ' AND carteira_id = :carteira_id';
            $params['carteira_id'] = $carteiraId;
        }
        if ($excludeTransactionId !== null) {
            $sql .= ' AND id != :exclude_id';
            $params['exclude_id'] = $excludeTransactionId;
        }
        $sql .= ' ORDER BY data_operacao ASC, id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function allChronological(?int $carteiraId = null): array
    {
        $sql = 'SELECT * FROM transacoes';
        $params = [];
        if ($carteiraId !== null) {
            $sql .= ' WHERE carteira_id = :carteira_id';
            $params['carteira_id'] = $carteiraId;
        }
        $sql .= ' ORDER BY data_operacao ASC, id ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM transacoes WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO transacoes (ativo_id, carteira_id, tipo, quantidade, preco_unitario, taxas, data_operacao, observacao)
             VALUES (:ativo_id, :carteira_id, :tipo, :quantidade, :preco_unitario, :taxas, :data_operacao, :observacao)'
        );
        $stmt->execute([
            'ativo_id' => (int)$data['ativo_id'],
            'carteira_id' => (int)$data['carteira_id'],
            'tipo' => $data['tipo'],
            'quantidade' => (int)$data['quantidade'],
            'preco_unitario' => parse_decimal_br($data['preco_unitario'] ?? 0),
            'taxas' => parse_decimal_br($data['taxas'] ?? 0),
            'data_operacao' => $data['data_operacao'],
            'observacao' => ($data['observacao'] ?? '') !== '' ? $data['observacao'] : null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE transacoes SET ativo_id = :ativo_id, carteira_id = :carteira_id, tipo = :tipo, quantidade = :quantidade,
             preco_unitario = :preco_unitario, taxas = :taxas, data_operacao = :data_operacao, observacao = :observacao
             WHERE id = :id'
        );
        return $stmt->execute([
            'ativo_id' => (int)$data['ativo_id'],
            'carteira_id' => (int)$data['carteira_id'],
            'tipo' => $data['tipo'],
            'quantidade' => (int)$data['quantidade'],
            'preco_unitario' => parse_decimal_br($data['preco_unitario'] ?? 0),
            'taxas' => parse_decimal_br($data['taxas'] ?? 0),
            'data_operacao' => $data['data_operacao'],
            'observacao' => ($data['observacao'] ?? '') !== '' ? $data['observacao'] : null,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM transacoes WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}

