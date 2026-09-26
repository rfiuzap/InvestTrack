<?php
declare(strict_types=1);

class DividendController
{
    public static function index(PDO $db): void
    {
        $carteiraSelecionada = resolve_carteira_id();
        $wallets = (new Wallet($db))->all();
        $proventos = (new Dividend($db))->all($carteiraSelecionada);

        $pageTitle = 'Proventos';
        require BASE_PATH . '/views/proventos/index.php';
    }

    public static function create(PDO $db): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::store($db);
            return;
        }
        $ativos = (new Asset($db))->all();
        $wallets = (new Wallet($db))->all();
        $carteiraSelecionada = resolve_carteira_id();
        $provento = null;
        $pageTitle = 'Novo Provento';
        require BASE_PATH . '/views/proventos/form.php';
    }

    public static function edit(PDO $db): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $dividendModel = new Dividend($db);
        $provento = $dividendModel->find($id);
        if (!$provento) {
            redirect('/proventos.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::update($db, $id);
            return;
        }

        $ativos = (new Asset($db))->all();
        $wallets = (new Wallet($db))->all();
        $pageTitle = 'Editar Provento';
        require BASE_PATH . '/views/proventos/form.php';
    }

    private static function validar(array $data): array
    {
        $erros = [];
        if (empty($data['ativo_id']) || !is_numeric($data['ativo_id'])) {
            $erros[] = 'Selecione um ativo válido.';
        }
        if (empty($data['carteira_id']) || !is_numeric($data['carteira_id'])) {
            $erros[] = 'Selecione a carteira responsável pelo provento.';
        }
        if (empty($data['tipo']) || !in_array($data['tipo'], ['DIVIDENDO', 'JCP', 'RENDIMENTO'], true)) {
            $erros[] = 'Tipo de provento inválido.';
        }
        if (!isset($data['valor']) || (float)$data['valor'] <= 0) {
            $erros[] = 'Valor deve ser maior que zero.';
        }
        if (empty($data['data_pagamento'])) {
            $erros[] = 'Data de pagamento é obrigatória.';
        }
        return $erros;
    }

    public static function store(PDO $db): void
    {
        if (!csrf_verify()) {
            flash('erro', 'Sessão expirada. Tente novamente.');
            redirect('/proventos.php?action=create');
        }

        $erros = self::validar($_POST);
        if (!empty($erros)) {
            flash('erro', implode(' ', $erros));
            redirect('/proventos.php?action=create');
        }

        (new Dividend($db))->create($_POST);

        flash('sucesso', 'Provento registrado com sucesso.');
        redirect('/proventos.php');
    }

    public static function update(PDO $db, int $id): void
    {
        if (!csrf_verify()) {
            flash('erro', 'Sessão expirada. Tente novamente.');
            redirect('/proventos.php?action=edit&id=' . $id);
        }

        $erros = self::validar($_POST);
        if (!empty($erros)) {
            flash('erro', implode(' ', $erros));
            redirect('/proventos.php?action=edit&id=' . $id);
        }

        (new Dividend($db))->update($id, $_POST);

        flash('sucesso', 'Provento atualizado com sucesso.');
        redirect('/proventos.php');
    }

    public static function delete(PDO $db): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            redirect('/proventos.php');
        }

        $id = (int)($_POST['id'] ?? 0);
        (new Dividend($db))->delete($id);

        flash('sucesso', 'Provento removido com sucesso.');
        redirect('/proventos.php');
    }
}
