<?php
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');
date_default_timezone_set('America/Sao_Paulo');

define('BASE_PATH', dirname(__DIR__));
define('BASE_URL', '/Projetos/InvestTrack%20Finance');
$versionFile = BASE_PATH . '/VERSION';
define('APP_VERSION', is_file($versionFile) ? trim((string)file_get_contents($versionFile)) : '0.0.0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function csrf_verify(): bool
{
    $token = $_POST['csrf_token'] ?? '';
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    header('Location: ' . BASE_URL . $path);
    exit;
}

function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

/**
 * Resolve a carteira selecionada via ?carteira= na URL ou persistida na sessão.
 * Afeta todas as páginas do sistema. Retorna null quando "todos" (visão consolidada) ou ausente.
 */
function resolve_carteira_id(): ?int
{
    if (isset($_GET['carteira'])) {
        $valor = $_GET['carteira'];
        if ($valor === 'todos' || $valor === '') {
            $_SESSION['carteira_id'] = null;
        } else {
            $_SESSION['carteira_id'] = (int)$valor;
        }
    }

    return isset($_SESSION['carteira_id']) && $_SESSION['carteira_id'] !== null ? (int)$_SESSION['carteira_id'] : null;
}

function carteira_badge_class(?string $nome): string
{
    $nomeLower = strtolower(trim((string)$nome));
    if (str_contains($nomeLower, 'renato')) {
        return 'bg-purple-50 text-purple-700 border border-purple-200/80';
    }
    if (str_contains($nomeLower, 'vicente')) {
        return 'bg-emerald-50 text-emerald-700 border border-emerald-200/80';
    }
    return 'bg-zinc-100 text-zinc-700 border border-zinc-200/80';
}

function carteira_badge(?string $nome): string
{
    $class = carteira_badge_class($nome);
    $dotColor = 'bg-zinc-400';
    $nomeLower = strtolower(trim((string)$nome));
    if (str_contains($nomeLower, 'renato')) {
        $dotColor = 'bg-purple-600';
    } elseif (str_contains($nomeLower, 'vicente')) {
        $dotColor = 'bg-emerald-500';
    }
    return '<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-0.5 text-xs font-medium ' . $class . '">'
         . '<span class="w-1.5 h-1.5 rounded-full ' . $dotColor . '"></span>'
         . e($nome)
         . '</span>';
}

/**
 * Formata preço unitário para exibição em tabelas/telas em pt-BR.
 * Exibe até 6 casas decimais, mantendo no mínimo 2 casas (ex: 35,50 ou 10,123456).
 */
function formatar_preco_unitario(mixed $valor): string
{
    if ($valor === null || $valor === '') {
        return '0,00';
    }
    $val = (float)str_replace(',', '.', (string)$valor);
    $precoStr = number_format($val, 6, ',', '.');
    $parts = explode(',', $precoStr);
    if (isset($parts[1])) {
        $dec = rtrim($parts[1], '0');
        if (strlen($dec) < 2) {
            $dec = str_pad($dec, 2, '0');
        }
        return $parts[0] . ',' . $dec;
    }
    return $precoStr;
}

/**
 * Formata preço unitário para preenchimento no atributo value de input HTML (com ponto).
 * Mantém até 6 casas decimais e no mínimo 2 casas (ex: 35.50 ou 10.123456).
 */
function formatar_preco_unitario_input(mixed $valor): string
{
    if ($valor === null || $valor === '') {
        return '';
    }
    $val = (float)str_replace(',', '.', (string)$valor);
    $precoStr = number_format($val, 6, '.', '');
    $trimmed = rtrim(rtrim($precoStr, '0'), '.');
    $parts = explode('.', $trimmed);
    if (!isset($parts[1])) {
        return $parts[0] . '.00';
    }
    if (strlen($parts[1]) < 2) {
        return $parts[0] . '.' . str_pad($parts[1], 2, '0');
    }
    return $trimmed;
}

/**
 * Converte valor em formato numérico brasileiro (ou internacional) para float com segurança.
 * Suporta "1.530,45", "1530,45", "10,123456", "35.50", "1.500.000,00".
 */
function parse_decimal_br(mixed $valor): float
{
    if ($valor === null || $valor === '') {
        return 0.0;
    }
    $str = trim((string)$valor);
    $str = preg_replace('/[^\d,\.\-]/', '', $str);
    if (str_contains($str, '.') && str_contains($str, ',')) {
        if (strrpos($str, ',') > strrpos($str, '.')) {
            $str = str_replace('.', '', $str);
            $str = str_replace(',', '.', $str);
        } else {
            $str = str_replace(',', '', $str);
        }
    } elseif (str_contains($str, ',')) {
        $str = str_replace(',', '.', $str);
    } elseif (substr_count($str, '.') > 1) {
        $str = str_replace('.', '', $str);
    }
    return (float)$str;
}

