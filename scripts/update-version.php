<?php
declare(strict_types=1);

$versionFile = dirname(__DIR__) . '/VERSION';
$releaseType = strtolower($argv[1] ?? 'patch');

if (!in_array($releaseType, ['major', 'minor', 'patch'], true)) {
    fwrite(STDERR, "Uso: php scripts/update-version.php [major|minor|patch]\n");
    exit(1);
}

if (!is_file($versionFile)) {
    fwrite(STDERR, "Arquivo VERSION não encontrado.\n");
    exit(1);
}

$currentVersion = trim((string)file_get_contents($versionFile));
if (!preg_match('/^(\\d+)\\.(\\d+)\\.(\\d+)$/', $currentVersion, $matches)) {
    fwrite(STDERR, "A versão atual deve seguir o formato MAJOR.MINOR.PATCH.\n");
    exit(1);
}

$major = (int)$matches[1];
$minor = (int)$matches[2];
$patch = (int)$matches[3];

if ($releaseType === 'major') {
    $major++;
    $minor = 0;
    $patch = 0;
} elseif ($releaseType === 'minor') {
    $minor++;
    $patch = 0;
} else {
    $patch++;
}

$newVersion = sprintf('%d.%d.%d', $major, $minor, $patch);
file_put_contents($versionFile, $newVersion . PHP_EOL);

printf("Versão atualizada de %s para %s\n", $currentVersion, $newVersion);
