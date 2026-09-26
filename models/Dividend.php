<?php
declare(strict_types=1);

class Dividend
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function all(?int $carteiraId = null): array
    {
        $sql = 'SELECT p.*, a.ticker, a.nome, a.tipo AS ativo_tipo, c.nome AS carteira_nome FROM proventos p
                JOIN ativos a ON a.id = p.ativo_id
                JOIN carteiras c ON c.id = p.carteira_id';
        $params = [];
        if ($carteiraId !== null) {
            $sql .= ' WHERE p.carteira_id = :carteira_id';
            $params['carteira_id'] = $carteiraId;
        }
        $sql .= ' ORDER BY p.data_pagamento DESC, p.id DESC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function allByAsset(int $ativoId, ?int $carteiraId = null): array
    {
        $sql = 'SELECT * FROM proventos WHERE ativo_id = :id';
        $params = ['id' => $ativoId];
        if ($carteiraId !== null) {
            $sql .= ' AND carteira_id = :carteira_id';
            $params['carteira_id'] = $carteiraId;
        }
        $sql .= ' ORDER BY data_pagamento ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function allChronological(?int $carteiraId = null): array
    {
        $sql = 'SELECT * FROM proventos';
        $params = [];
        if ($carteiraId !== null) {
            $sql .= ' WHERE carteira_id = :carteira_id';
            $params['carteira_id'] = $carteiraId;
        }
        $sql .= ' ORDER BY data_pagamento ASC';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM proventos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO proventos (ativo_id, carteira_id, tipo, valor, data_pagamento, observacao)
             VALUES (:ativo_id, :carteira_id, :tipo, :valor, :data_pagamento, :observacao)'
        );
        $stmt->execute([
            'ativo_id' => (int)$data['ativo_id'],
            'carteira_id' => (int)$data['carteira_id'],
            'tipo' => $data['tipo'],
            'valor' => (float)$data['valor'],
            'data_pagamento' => $data['data_pagamento'],
            'observacao' => ($data['observacao'] ?? '') !== '' ? $data['observacao'] : null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE proventos SET ativo_id = :ativo_id, carteira_id = :carteira_id, tipo = :tipo, valor = :valor,
             data_pagamento = :data_pagamento, observacao = :observacao WHERE id = :id'
        );
        return $stmt->execute([
            'ativo_id' => (int)$data['ativo_id'],
            'carteira_id' => (int)$data['carteira_id'],
            'tipo' => $data['tipo'],
            'valor' => (float)$data['valor'],
            'data_pagamento' => $data['data_pagamento'],
            'observacao' => ($data['observacao'] ?? '') !== '' ? $data['observacao'] : null,
            'id' => $id,
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM proventos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
