<?php
declare(strict_types=1);

class TransactionController
{
    public static function index(PDO $db): void
    {
        $carteiraSelecionada = resolve_carteira_id();
        $wallets = (new Wallet($db))->all();

        $transactionModel = new Transaction($db);
        $transacoes = $transactionModel->all($carteiraSelecionada);

        $pageTitle = 'Operações';
        require BASE_PATH . '/views/operacoes/index.php';
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
        $transacao = null;
        $pageTitle = 'Nova Operação';
        require BASE_PATH . '/views/operacoes/form.php';
    }

    public static function edit(PDO $db): void
    {
        $id = (int)($_GET['id'] ?? 0);
        $transactionModel = new Transaction($db);
        $transacao = $transactionModel->find($id);
        if (!$transacao) {
            redirect('/operacoes.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::update($db, $id);
            return;
        }

        $ativos = (new Asset($db))->all();
        $wallets = (new Wallet($db))->all();
        $pageTitle = 'Editar Operação';
        require BASE_PATH . '/views/operacoes/form.php';
    }

    private static function validar(array $data): array
    {
        $erros = [];
        if (empty($data['ativo_id']) || !is_numeric($data['ativo_id'])) {
            $erros[] = 'Selecione um ativo válido.';
        }
        if (empty($data['carteira_id']) || !is_numeric($data['carteira_id'])) {
            $erros[] = 'Selecione a carteira responsável pela operação.';
        }
        if (empty($data['tipo']) || !in_array($data['tipo'], ['COMPRA', 'VENDA'], true)) {
            $erros[] = 'Tipo de operação inválido.';
        }
        if (empty($data['quantidade']) || (int)$data['quantidade'] <= 0) {
            $erros[] = 'Quantidade deve ser maior que zero.';
        }
        $precoNorm = parse_decimal_br($data['preco_unitario'] ?? '');
        if (!isset($data['preco_unitario']) || $precoNorm <= 0) {
            $erros[] = 'Preço unitário deve ser maior que zero.';
        }
        if (empty($data['data_operacao'])) {
            $erros[] = 'Data da operação é obrigatória.';
        }
        return $erros;
    }

    public static function store(PDO $db): void
    {
        if (!csrf_verify()) {
            flash('erro', 'Sessão expirada. Tente novamente.');
            redirect('/operacoes.php?action=create');
        }

        $erros = self::validar($_POST);
        if (!empty($erros)) {
            flash('erro', implode(' ', $erros));
            redirect('/operacoes.php?action=create');
        }

        if (($_POST['tipo'] ?? '') === 'VENDA' && empty($_POST['bypass_posicao'])) {
            $ativo = (new Asset($db))->find((int)($_POST['ativo_id'] ?? 0));
            if ($ativo) {
                $posicao = (new PortfolioService($db))->calcularPosicaoAtivo($ativo, (int)($_POST['carteira_id'] ?? 0));
                $qtdDesejada = (int)($_POST['quantidade'] ?? 0);
                if ($posicao['quantidade'] < $qtdDesejada) {
                    $msg = $posicao['quantidade'] <= 0 
                        ? 'Você não possui este ativo nesta carteira para realizar a venda.' 
                        : 'Quantidade insuficiente em carteira (' . $posicao['quantidade'] . ' disponíveis).';
                    flash('alerta_posicao', $msg);
                    flash('erro', $msg);
                    redirect('/operacoes.php?action=create');
                }
            }
        }

        (new Transaction($db))->create($_POST);

        flash('sucesso', 'Operação registrada com sucesso.');
        redirect('/operacoes.php');
    }

    public static function update(PDO $db, int $id): void
    {
        if (!csrf_verify()) {
            flash('erro', 'Sessão expirada. Tente novamente.');
            redirect('/operacoes.php?action=edit&id=' . $id);
        }

        $erros = self::validar($_POST);
        if (!empty($erros)) {
            flash('erro', implode(' ', $erros));
            redirect('/operacoes.php?action=edit&id=' . $id);
        }

        if (($_POST['tipo'] ?? '') === 'VENDA' && empty($_POST['bypass_posicao'])) {
            $ativo = (new Asset($db))->find((int)($_POST['ativo_id'] ?? 0));
            if ($ativo) {
                $posicao = (new PortfolioService($db))->calcularPosicaoAtivo($ativo, (int)($_POST['carteira_id'] ?? 0), $id);
                $qtdDesejada = (int)($_POST['quantidade'] ?? 0);
                if ($posicao['quantidade'] < $qtdDesejada) {
                    $msg = $posicao['quantidade'] <= 0 
                        ? 'Você não possui este ativo nesta carteira para realizar a venda.' 
                        : 'Quantidade insuficiente em carteira (' . $posicao['quantidade'] . ' disponíveis).';
                    flash('alerta_posicao', $msg);
                    flash('erro', $msg);
                    redirect('/operacoes.php?action=edit&id=' . $id);
                }
            }
        }

        (new Transaction($db))->update($id, $_POST);

        flash('sucesso', 'Operação atualizada com sucesso.');
        redirect('/operacoes.php');
    }

    public static function delete(PDO $db): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_verify()) {
            redirect('/operacoes.php');
        }

        $id = (int)($_POST['id'] ?? 0);
        (new Transaction($db))->delete($id);

        flash('sucesso', 'Operação removida com sucesso.');
        redirect('/operacoes.php');
    }

    public static function templateCsv(): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="modelo_importacao_operacoes.csv"');

        echo "\xEF\xBB\xBF";
        echo "Data;Carteira;Ticker;Tipo;Quantidade;Preco Unitario;Taxas;Observacao\n";
        echo "22/09/2026;Renato;PETR4;COMPRA;100;37.50;5.00;Compra mensal\n";
        echo "15/09/2026;Vicente;HGLG11;COMPRA;50;165.20;0.00;Aporte FII\n";
        echo "10/09/2026;Renato;VALE3;VENDA;20;58.90;2.50;Rebalanceamento\n";
        exit;
    }

    public static function import(PDO $db): void
    {
        $wallets = (new Wallet($db))->all();
        $carteiraSelecionada = resolve_carteira_id();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!csrf_verify()) {
                flash('erro', 'Sessão expirada. Tente novamente.');
                redirect('/operacoes.php?action=import');
            }

            if (empty($_FILES['arquivo_csv']['tmp_name']) || $_FILES['arquivo_csv']['error'] !== UPLOAD_ERR_OK) {
                flash('erro', 'Nenhum arquivo CSV válido foi enviado.');
                redirect('/operacoes.php?action=import');
            }

            $carteiraPadraoId = (int)($_POST['carteira_id'] ?? 0);
            $handle = fopen($_FILES['arquivo_csv']['tmp_name'], 'r');
            if (!$handle) {
                flash('erro', 'Não foi possível ler o arquivo enviado.');
                redirect('/operacoes.php?action=import');
            }

            $primeiraLinha = fgets($handle);
            if ($primeiraLinha === false) {
                fclose($handle);
                flash('erro', 'O arquivo CSV está vazio.');
                redirect('/operacoes.php?action=import');
            }

            // Remove UTF-8 BOM
            $primeiraLinha = preg_replace('/^\xEF\xBB\xBF/', '', $primeiraLinha);

            // Detectar delimitador: ; ou , ou \t
            $delimitador = ';';
            if (substr_count($primeiraLinha, ',') > substr_count($primeiraLinha, ';')) {
                $delimitador = ',';
            } elseif (substr_count($primeiraLinha, "\t") > substr_count($primeiraLinha, ';')) {
                $delimitador = "\t";
            }

            $colunas = str_getcsv($primeiraLinha, $delimitador, '"', "\\");
            $map = [];
            foreach ($colunas as $idx => $nomeCol) {
                $normalizado = strtolower(trim((string)$nomeCol));
                $normalizado = strtr(utf8_decode($normalizado), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿ'), 'aaaaaceeeeiiiinooooouuuuyy');
                $normalizado = preg_replace('/[^a-z0-9]/', '', $normalizado);

                if (in_array($normalizado, ['data', 'datadonegocio', 'datadaoperacao', 'datapregao', 'date'], true)) {
                    $map['data'] = $idx;
                } elseif (in_array($normalizado, ['ticker', 'codigo', 'codigodenegociacao', 'ativo', 'papel', 'symbol'], true)) {
                    $map['ticker'] = $idx;
                } elseif (in_array($normalizado, ['tipo', 'operacao', 'tipodeoperacao', 'compravenda', 'cv', 'side'], true)) {
                    $map['tipo'] = $idx;
                } elseif (in_array($normalizado, ['quantidade', 'qtd', 'quant', 'quantidadenegociada'], true)) {
                    $map['quantidade'] = $idx;
                } elseif (in_array($normalizado, ['preco', 'precounitario', 'precomedio', 'precocompra', 'price'], true)) {
                    $map['preco'] = $idx;
                } elseif (in_array($normalizado, ['taxas', 'custos', 'corretagem', 'emolumentos', 'fees'], true)) {
                    $map['taxas'] = $idx;
                } elseif (in_array($normalizado, ['carteira', 'wallet', 'carteiraid', 'titular'], true)) {
                    $map['carteira'] = $idx;
                } elseif (in_array($normalizado, ['observacao', 'obs', 'nota'], true)) {
                    $map['observacao'] = $idx;
                }
            }

            if (!isset($map['ticker'], $map['tipo'], $map['quantidade'], $map['preco'])) {
                fclose($handle);
                flash('erro', 'O arquivo CSV precisa ter ao menos as colunas: Ticker, Tipo (Compra/Venda), Quantidade e Preço.');
                redirect('/operacoes.php?action=import');
            }

            $carteirasPorNome = [];
            foreach ($wallets as $w) {
                $carteirasPorNome[strtolower(trim($w['nome']))] = (int)$w['id'];
            }

            $assetModel = new Asset($db);
            $transactionModel = new Transaction($db);
            $operacoesParaInserir = [];
            $linhaNum = 1;
            $errosLinhas = [];

            while (($row = fgetcsv($handle, 4096, $delimitador, '"', "\\")) !== false) {
                $linhaNum++;
                if (empty(array_filter($row))) {
                    continue;
                }

                $ticker = isset($map['ticker']) ? strtoupper(trim((string)($row[$map['ticker']] ?? ''))) : '';
                if ($ticker === '') {
                    continue;
                }

                $carteiraId = $carteiraPadraoId;
                if (isset($map['carteira']) && !empty($row[$map['carteira']])) {
                    $cartVal = strtolower(trim((string)$row[$map['carteira']]));
                    if (isset($carteirasPorNome[$cartVal])) {
                        $carteiraId = $carteirasPorNome[$cartVal];
                    } elseif (is_numeric($cartVal) && in_array((int)$cartVal, array_values($carteirasPorNome), true)) {
                        $carteiraId = (int)$cartVal;
                    }
                }

                if ($carteiraId <= 0) {
                    if (!empty($wallets)) {
                        $carteiraId = (int)$wallets[0]['id'];
                    } else {
                        $errosLinhas[] = "Linha $linhaNum: Carteira não especificada para $ticker.";
                        continue;
                    }
                }

                $tipoBruto = isset($map['tipo']) ? strtoupper(trim((string)($row[$map['tipo']] ?? ''))) : 'COMPRA';
                $tipo = (str_starts_with($tipoBruto, 'V') || str_contains($tipoBruto, 'VEN')) ? 'VENDA' : 'COMPRA';

                $qtdStr = isset($map['quantidade']) ? trim((string)($row[$map['quantidade']] ?? '0')) : '0';
                $qtd = (int)str_replace(['.', ','], '', $qtdStr);
                if ($qtd <= 0) {
                    $errosLinhas[] = "Linha $linhaNum: Quantidade inválida para $ticker ($qtdStr).";
                    continue;
                }

                $precoStr = isset($map['preco']) ? trim((string)($row[$map['preco']] ?? '0')) : '0';
                $precoStr = str_replace('R$', '', $precoStr);
                $precoStr = trim($precoStr);
                if (str_contains($precoStr, ',') && str_contains($precoStr, '.')) {
                    $precoStr = str_replace('.', '', $precoStr);
                    $precoStr = str_replace(',', '.', $precoStr);
                } elseif (str_contains($precoStr, ',')) {
                    $precoStr = str_replace(',', '.', $precoStr);
                }
                $preco = (float)$precoStr;
                if ($preco <= 0) {
                    $errosLinhas[] = "Linha $linhaNum: Preço unitário inválido para $ticker ($precoStr).";
                    continue;
                }

                $taxas = 0.0;
                if (isset($map['taxas']) && !empty($row[$map['taxas']])) {
                    $tStr = trim((string)$row[$map['taxas']]);
                    $tStr = str_replace(['R$', ' '], '', $tStr);
                    if (str_contains($tStr, ',') && str_contains($tStr, '.')) {
                        $tStr = str_replace('.', '', $tStr);
                        $tStr = str_replace(',', '.', $tStr);
                    } elseif (str_contains($tStr, ',')) {
                        $tStr = str_replace(',', '.', $tStr);
                    }
                    $taxas = max(0.0, (float)$tStr);
                }

                $dataStr = isset($map['data']) ? trim((string)($row[$map['data']] ?? '')) : '';
                $dataOperacao = date('Y-m-d');
                if ($dataStr !== '') {
                    if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/', $dataStr, $m)) {
                        $dataOperacao = sprintf('%04d-%02d-%02d', (int)$m[3], (int)$m[2], (int)$m[1]);
                    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataStr)) {
                        $dataOperacao = $dataStr;
                    }
                }

                $obs = isset($map['observacao']) ? trim((string)($row[$map['observacao']] ?? '')) : 'Importado via CSV';

                $ativo = $assetModel->findByTicker($ticker);
                if (!$ativo) {
                    $tipoAtivo = 'ACAO';
                    if (str_ends_with($ticker, '11')) {
                        $tipoAtivo = 'FII';
                    } elseif (str_ends_with($ticker, '34') || str_ends_with($ticker, '35')) {
                        $tipoAtivo = 'BDR';
                    } elseif (str_ends_with($ticker, '39')) {
                        $tipoAtivo = 'ETF';
                    }

                    $novoAtivoId = $assetModel->create([
                        'ticker' => $ticker,
                        'nome' => $ticker . ' (Importado B3)',
                        'tipo' => $tipoAtivo,
                    ]);
                    $ativoId = $novoAtivoId;
                } else {
                    $ativoId = (int)$ativo['id'];
                }

                $operacoesParaInserir[] = [
                    'ativo_id' => $ativoId,
                    'carteira_id' => $carteiraId,
                    'tipo' => $tipo,
                    'quantidade' => $qtd,
                    'preco_unitario' => $preco,
                    'taxas' => $taxas,
                    'data_operacao' => $dataOperacao,
                    'observacao' => $obs,
                ];
            }

            fclose($handle);

            if (empty($operacoesParaInserir)) {
                $msgErro = !empty($errosLinhas) ? implode(' ', array_slice($errosLinhas, 0, 3)) : 'Nenhuma operação válida encontrada no arquivo CSV.';
                flash('erro', $msgErro);
                redirect('/operacoes.php?action=import');
            }

            $db->beginTransaction();
            try {
                foreach ($operacoesParaInserir as $op) {
                    $transactionModel->create($op);
                }
                $db->commit();
                $totalInseridas = count($operacoesParaInserir);
                $msgSucesso = "$totalInseridas operações foram importadas com sucesso!";
                if (!empty($errosLinhas)) {
                    $msgSucesso .= ' (Algumas linhas com dados inválidos foram ignoradas).';
                }
                flash('sucesso', $msgSucesso);
                redirect('/operacoes.php');
            } catch (Throwable $e) {
                $db->rollBack();
                flash('erro', 'Erro ao salvar operações no banco de dados: ' . $e->getMessage());
                redirect('/operacoes.php?action=import');
            }
        }

        $pageTitle = 'Importar Operações (CSV)';
        require BASE_PATH . '/views/operacoes/import.php';
    }
}
