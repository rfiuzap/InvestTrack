<?php
declare(strict_types=1);

class WalletController
{
    public static function index(PDO $db): void
    {
        $wallets = (new Wallet($db))->allIncludingInactive();
        $pageTitle = 'Carteiras';
        require BASE_PATH . '/views/carteiras/index.php';
    }

    public static function store(PDO $db): void
    {
        if (!csrf_verify()) {
            flash('erro', 'Sessão expirada. Tente novamente.');
            redirect('/carteiras.php');
        }

        $nome = trim((string)($_POST['nome'] ?? ''));
        $nome = preg_replace('/\s+/', ' ', $nome) ?? $nome;
        if (strlen($nome) < 2 || strlen($nome) > 80) {
            flash('erro', 'Informe um nome de carteira entre 2 e 80 caracteres.');
            redirect('/carteiras.php');
        }

        try {
            (new Wallet($db))->create($nome);
            flash('sucesso', 'Carteira criada com sucesso.');
        } catch (PDOException $e) {
            if ((int)$e->errorInfo[1] === 19) {
                flash('erro', 'Já existe uma carteira com esse nome.');
            } else {
                throw $e;
            }
        }
        redirect('/carteiras.php');
    }

    public static function deactivate(PDO $db): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            redirect('/carteiras.php');
        }

        $id = (int)($_POST['id'] ?? 0);
        $walletModel = new Wallet($db);
        $wallet = $walletModel->find($id);
        if (!$wallet || (int)$wallet['ativa'] !== 1) {
            flash('erro', 'Carteira não encontrada ou já inativa.');
            redirect('/carteiras.php');
        }

        $walletModel->deactivate($id);
        if (resolve_carteira_id() === $id) {
            $_SESSION['carteira_id'] = null;
        }
        flash('sucesso', 'Carteira inativada. O histórico foi preservado.');
        redirect('/carteiras.php');
    }

}