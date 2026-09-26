-- InvestTrack Finance - Banco limpo para a versão publicada no GitHub
-- Este arquivo não contém nenhum registro do banco local.
-- Use-o em uma instalação separada para preservar o banco investtrack local.

CREATE DATABASE IF NOT EXISTS investtrack_github CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE investtrack_github;

CREATE TABLE IF NOT EXISTS carteiras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(60) NOT NULL UNIQUE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ativos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticker VARCHAR(10) NOT NULL UNIQUE,
    nome VARCHAR(150) NOT NULL,
    tipo ENUM('ACAO','FII','ETF','BDR','OUTRO') NOT NULL DEFAULT 'ACAO',
    setor VARCHAR(100) DEFAULT NULL,
    cotacao_atual DECIMAL(14,4) NOT NULL DEFAULT 0,
    cotacao_atualizada_em DATETIME DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS transacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ativo_id INT NOT NULL,
    carteira_id INT NOT NULL,
    tipo ENUM('COMPRA','VENDA') NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(18,6) NOT NULL,
    taxas DECIMAL(14,4) NOT NULL DEFAULT 0,
    data_operacao DATE NOT NULL,
    observacao VARCHAR(255) DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_transacoes_ativo FOREIGN KEY (ativo_id) REFERENCES ativos(id) ON DELETE CASCADE,
    CONSTRAINT fk_transacoes_carteira FOREIGN KEY (carteira_id) REFERENCES carteiras(id),
    INDEX idx_transacoes_ativo (ativo_id),
    INDEX idx_transacoes_carteira (carteira_id),
    INDEX idx_transacoes_data (data_operacao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS proventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ativo_id INT NOT NULL,
    carteira_id INT NOT NULL,
    tipo ENUM('DIVIDENDO','JCP','RENDIMENTO') NOT NULL DEFAULT 'DIVIDENDO',
    valor DECIMAL(14,4) NOT NULL,
    data_pagamento DATE NOT NULL,
    observacao VARCHAR(255) DEFAULT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_proventos_ativo FOREIGN KEY (ativo_id) REFERENCES ativos(id) ON DELETE CASCADE,
    CONSTRAINT fk_proventos_carteira FOREIGN KEY (carteira_id) REFERENCES carteiras(id),
    INDEX idx_proventos_ativo (ativo_id),
    INDEX idx_proventos_carteira (carteira_id),
    INDEX idx_proventos_data (data_pagamento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO carteiras (nome) VALUES
    ('João'),
    ('Maria');

INSERT INTO ativos (ticker, nome, tipo, setor, cotacao_atual, cotacao_atualizada_em) VALUES
    ('ABEV3', 'Ambev ON', 'ACAO', 'Bebidas', 13.42, NOW()),
    ('BBAS3', 'Banco do Brasil ON', 'ACAO', 'Financeiro', 27.85, NOW()),
    ('BBDC4', 'Bradesco PN', 'ACAO', 'Financeiro', 15.76, NOW()),
    ('ITUB4', 'Itaú Unibanco PN', 'ACAO', 'Financeiro', 36.18, NOW()),
    ('LREN3', 'Lojas Renner ON', 'ACAO', 'Varejo', 17.94, NOW()),
    ('PETR4', 'Petrobras PN', 'ACAO', 'Petróleo e Gás', 38.50, NOW()),
    ('PRIO3', 'PRIO ON', 'ACAO', 'Petróleo e Gás', 42.31, NOW()),
    ('RENT3', 'Localiza ON', 'ACAO', 'Aluguel de Veículos', 49.67, NOW()),
    ('VALE3', 'Vale ON', 'ACAO', 'Mineração', 62.44, NOW()),
    ('WEGE3', 'WEG ON', 'ACAO', 'Bens Industriais', 41.28, NOW());

-- Dez posições de teste para cada carteira, totalizando vinte operações.
INSERT INTO transacoes (ativo_id, carteira_id, tipo, quantidade, preco_unitario, taxas, data_operacao, observacao) VALUES
    ((SELECT id FROM ativos WHERE ticker = 'ABEV3'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 100, 12.90, 2.50, '2025-01-15', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'BBAS3'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 50, 25.40, 2.50, '2025-02-10', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'BBDC4'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 80, 14.85, 2.50, '2025-03-12', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'ITUB4'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 60, 32.70, 2.50, '2025-04-08', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'LREN3'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 75, 18.20, 2.50, '2025-05-19', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'PETR4'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 100, 35.10, 2.50, '2025-06-23', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'PRIO3'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 40, 39.80, 2.50, '2025-07-14', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'RENT3'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 30, 51.25, 2.50, '2025-08-05', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'VALE3'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 70, 58.90, 2.50, '2025-09-17', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'WEGE3'), (SELECT id FROM carteiras WHERE nome = 'João'), 'COMPRA', 45, 38.60, 2.50, '2025-10-21', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'ABEV3'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 120, 12.75, 2.50, '2025-01-20', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'BBAS3'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 65, 24.90, 2.50, '2025-02-18', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'BBDC4'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 90, 14.40, 2.50, '2025-03-25', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'ITUB4'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 50, 31.95, 2.50, '2025-04-15', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'LREN3'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 60, 17.65, 2.50, '2025-05-27', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'PETR4'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 80, 34.70, 2.50, '2025-06-30', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'PRIO3'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 55, 37.95, 2.50, '2025-07-22', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'RENT3'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 35, 48.80, 2.50, '2025-08-19', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'VALE3'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 85, 57.30, 2.50, '2025-09-29', 'Dados de demonstração'),
    ((SELECT id FROM ativos WHERE ticker = 'WEGE3'), (SELECT id FROM carteiras WHERE nome = 'Maria'), 'COMPRA', 50, 36.90, 2.50, '2025-10-28', 'Dados de demonstração');
