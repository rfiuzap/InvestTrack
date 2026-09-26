<?php
declare(strict_types=1);

class AssetController
{
    public static function index(PDO $db): void
    {
        $carteiraSelecionada = resolve_carteira_id();
        $wallets = (new Wallet($db))->all();

        $portfolioService = new PortfolioService($db);
        $posicoes = $portfolioService->calcularCarteira($carteiraSelecionada);

        $pageTitle = 'Ativos';
        require BASE_PATH . '/views/ativos/index.php';
    }

    public static function create(PDO $db): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::store($db);
            return;
        }
        $ativo = null;
        $pageTitle = 'Novo Ativo';
        require BASE_PATH . '/views/ativos/form.php';
    }

    public static function edit(PDO $db): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $assetModel = new Asset($db);
        $ativo = $assetModel->find($id);
        if (!$ativo) {
            redirect('/ativos.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::update($db, $id);
            return;
        }

        $pageTitle = 'Editar Ativo';
        require BASE_PATH . '/views/ativos/form.php';
    }

    private static function validar(array $data): array
    {
        $erros = [];
        if (empty($data['ticker']) || !preg_match('/^[A-Z0-9]{4,8}$/', strtoupper(trim($data['ticker'])))) {
            $erros[] = 'Ticker inválido. Use o código B3 (ex: PETR4).';
        }
        if (empty($data['nome']) || strlen(trim($data['nome'])) < 2) {
            $erros[] = 'Nome do ativo é obrigatório.';
        }
        if (empty($data['tipo']) || !in_array($data['tipo'], ['ACAO', 'FII', 'ETF', 'BDR', 'OUTRO'], true)) {
            $erros[] = 'Tipo de ativo inválido.';
        }
        return $erros;
    }

    public static function store(PDO $db): void
    {
        if (!csrf_verify()) {
            flash('erro', 'Sessão expirada. Tente novamente.');
            redirect('/ativos.php?action=create');
        }

        $erros = self::validar($_POST);
        $assetModel = new Asset($db);

        if ($assetModel->findByTicker($_POST['ticker'] ?? '')) {
            $erros[] = 'Já existe um ativo cadastrado com este ticker.';
        }

        if (!empty($erros)) {
            flash('erro', implode(' ', $erros));
            redirect('/ativos.php?action=create');
        }

        $id = $assetModel->create($_POST);
        if (!empty($_POST['cotacao_atual']) && is_numeric($_POST['cotacao_atual'])) {
            $assetModel->updateQuote($id, (float)$_POST['cotacao_atual']);
        }

        flash('sucesso', 'Ativo cadastrado com sucesso.');
        redirect('/ativos.php');
    }

    public static function update(PDO $db, int $id): void
    {
        if (!csrf_verify()) {
            flash('erro', 'Sessão expirada. Tente novamente.');
            redirect('/ativos.php?action=edit&id=' . $id);
        }

        $erros = self::validar($_POST);
        $assetModel = new Asset($db);

        $existente = $assetModel->findByTicker($_POST['ticker'] ?? '');
        if ($existente && (int)$existente['id'] !== $id) {
            $erros[] = 'Já existe outro ativo cadastrado com este ticker.';
        }

        if (!empty($erros)) {
            flash('erro', implode(' ', $erros));
            redirect('/ativos.php?action=edit&id=' . $id);
        }

        $assetModel->update($id, $_POST);
        if (isset($_POST['cotacao_atual']) && is_numeric($_POST['cotacao_atual'])) {
            $assetModel->updateQuote($id, (float)$_POST['cotacao_atual']);
        }

        flash('sucesso', 'Ativo atualizado com sucesso.');
        redirect('/ativos.php');
    }

    public static function delete(PDO $db): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            redirect('/ativos.php');
        }

        $id = (int)($_POST['id'] ?? 0);
        $assetModel = new Asset($db);

        if ($assetModel->hasTransactions($id)) {
            flash('erro', 'Não é possível excluir um ativo que possui operações registradas.');
            redirect('/ativos.php');
        }

        $assetModel->delete($id);
        flash('sucesso', 'Ativo removido com sucesso.');
        redirect('/ativos.php');
    }
}
