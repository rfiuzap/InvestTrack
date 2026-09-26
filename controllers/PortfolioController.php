<?php
declare(strict_types=1);

class PortfolioController
{
    public static function relatorios(PDO $db): void
    {
        $carteiraSelecionada = resolve_carteira_id();
        $wallets = (new Wallet($db))->all();

        $portfolioService = new PortfolioService($db);
        $resumo = $portfolioService->resumoGeral($carteiraSelecionada);
        $proventos = (new Dividend($db))->all($carteiraSelecionada);

        $anoFiltroRenda = isset($_GET['ano_renda']) ? (int)$_GET['ano_renda'] : null;
        $rendaPassiva = $portfolioService->relatorioRendaPassiva($carteiraSelecionada, $anoFiltroRenda);

        $anoFiltroIRPF = isset($_GET['ano_irpf']) ? (int)$_GET['ano_irpf'] : (int)date('Y');
        $relatorioIRPF = $portfolioService->relatorioFiscalIRPF($anoFiltroIRPF, $carteiraSelecionada);

        $abaAtiva = $_GET['tab'] ?? 'desempenho';

        $pageTitle = 'Relatórios';
        require BASE_PATH . '/views/relatorios/index.php';
    }
}
