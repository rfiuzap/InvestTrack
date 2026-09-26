<?php
declare(strict_types=1);

class DashboardController
{
    public static function index(PDO $db): void
    {
        $carteiraSelecionada = resolve_carteira_id();
        $wallets = (new Wallet($db))->all();

        $portfolioService = new PortfolioService($db);
        $resumo = $portfolioService->resumoGeral($carteiraSelecionada);
        $alocacao = $portfolioService->alocacaoPorAtivo($carteiraSelecionada, $resumo['posicoes']);
        $evolucao = $portfolioService->evolucaoMensal($carteiraSelecionada);

        $pageTitle = 'Dashboard';
        require BASE_PATH . '/views/dashboard.php';
    }
}
