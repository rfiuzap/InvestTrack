CREATE TABLE IF NOT EXISTS carteiras (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nome TEXT NOT NULL UNIQUE,
    ativa INTEGER NOT NULL DEFAULT 1 CHECK (ativa IN (0, 1)),
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ativos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ticker TEXT NOT NULL UNIQUE,
    nome TEXT NOT NULL,
    tipo TEXT NOT NULL DEFAULT 'ACAO' CHECK (tipo IN ('ACAO', 'FII', 'ETF', 'BDR', 'OUTRO')),
    setor TEXT DEFAULT NULL,
    cotacao_atual NUMERIC NOT NULL DEFAULT 0,
    cotacao_atualizada_em TEXT DEFAULT NULL,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS transacoes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ativo_id INTEGER NOT NULL,
    carteira_id INTEGER NOT NULL,
    tipo TEXT NOT NULL CHECK (tipo IN ('COMPRA', 'VENDA')),
    quantidade INTEGER NOT NULL,
    preco_unitario NUMERIC NOT NULL,
    taxas NUMERIC NOT NULL DEFAULT 0,
    data_operacao TEXT NOT NULL,
    observacao TEXT DEFAULT NULL,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id) ON DELETE CASCADE,
    FOREIGN KEY (carteira_id) REFERENCES carteiras(id)
);

CREATE TABLE IF NOT EXISTS proventos (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ativo_id INTEGER NOT NULL,
    carteira_id INTEGER NOT NULL,
    tipo TEXT NOT NULL DEFAULT 'DIVIDENDO' CHECK (tipo IN ('DIVIDENDO', 'JCP', 'RENDIMENTO')),
    valor NUMERIC NOT NULL,
    data_pagamento TEXT NOT NULL,
    observacao TEXT DEFAULT NULL,
    criado_em TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ativo_id) REFERENCES ativos(id) ON DELETE CASCADE,
    FOREIGN KEY (carteira_id) REFERENCES carteiras(id)
);

CREATE INDEX IF NOT EXISTS idx_transacoes_ativo ON transacoes (ativo_id);
CREATE INDEX IF NOT EXISTS idx_transacoes_carteira ON transacoes (carteira_id);
CREATE INDEX IF NOT EXISTS idx_transacoes_data ON transacoes (data_operacao);
CREATE INDEX IF NOT EXISTS idx_proventos_ativo ON proventos (ativo_id);
CREATE INDEX IF NOT EXISTS idx_proventos_carteira ON proventos (carteira_id);
CREATE INDEX IF NOT EXISTS idx_proventos_data ON proventos (data_pagamento);

CREATE TABLE IF NOT EXISTS demo_controle (
    id INTEGER PRIMARY KEY CHECK (id = 1),
    reiniciado_em INTEGER NOT NULL
);