-- Seed demonstrativo aplicado pelo aplicativo quando banco_producao.sqlite esta vazio.

INSERT OR IGNORE INTO carteiras (nome) VALUES
    ('João'),
    ('Maria');

INSERT OR IGNORE INTO ativos (ticker, nome, tipo, setor, cotacao_atual, cotacao_atualizada_em) VALUES
    ('ABEV3', 'Ambev ON', 'ACAO', 'Bebidas', 13.42, datetime('now', 'localtime')),
    ('BBAS3', 'Banco do Brasil ON', 'ACAO', 'Financeiro', 27.85, datetime('now', 'localtime')),
    ('BBDC4', 'Bradesco PN', 'ACAO', 'Financeiro', 15.76, datetime('now', 'localtime')),
    ('ITUB4', 'Itaú Unibanco PN', 'ACAO', 'Financeiro', 36.18, datetime('now', 'localtime')),
    ('LREN3', 'Lojas Renner ON', 'ACAO', 'Varejo', 17.94, datetime('now', 'localtime')),
    ('PETR4', 'Petrobras PN', 'ACAO', 'Petróleo e Gás', 38.50, datetime('now', 'localtime')),
    ('PRIO3', 'PRIO ON', 'ACAO', 'Petróleo e Gás', 42.31, datetime('now', 'localtime')),
    ('RENT3', 'Localiza ON', 'ACAO', 'Aluguel de Veículos', 49.67, datetime('now', 'localtime')),
    ('VALE3', 'Vale ON', 'ACAO', 'Mineração', 62.44, datetime('now', 'localtime')),
    ('WEGE3', 'WEG ON', 'ACAO', 'Bens Industriais', 41.28, datetime('now', 'localtime'));

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
