<?php

declare(strict_types=1);

$apiRoot = dirname(__DIR__);

require_once $apiRoot . '/bootstrap/autoload.php';
require_once $apiRoot . '/bootstrap/env.php';
require_once __DIR__ . '/SqlDialectTransformer.php';

fox_api_load_env($apiRoot);

$containerFactory = require $apiRoot . '/bootstrap/container.php';
$container = $containerFactory($apiRoot);
$pdo = $container->get(\FoxPlatform\Api\Infrastructure\Persistence\DatabaseConnection::class)->pdo();
$driver = (string) $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);

$pdo->exec(
    $driver === 'mysql'
        ? 'CREATE TABLE IF NOT EXISTS seed_runs (
            filename VARCHAR(255) PRIMARY KEY,
            executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        )'
        : 'CREATE TABLE IF NOT EXISTS seed_runs (
            filename VARCHAR(255) PRIMARY KEY,
            executed_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )'
);

$files = glob($apiRoot . '/database/seeders/*.sql') ?: [];
sort($files);

foreach ($files as $file) {
    $filename = basename($file);
    $statement = $pdo->prepare('SELECT 1 FROM seed_runs WHERE filename = :filename');
    $statement->execute(['filename' => $filename]);

    if ($statement->fetchColumn()) {
        echo "[skip] {$filename}" . PHP_EOL;
        continue;
    }

    echo "[run] {$filename}" . PHP_EOL;
    $sql = file_get_contents($file);
    $sql = \FoxPlatform\Api\Scripts\SqlDialectTransformer::transform((string) $sql, $driver);

    try {
        if ($driver !== 'mysql') {
            $pdo->beginTransaction();
        }
        $pdo->exec($sql);
        $insert = $pdo->prepare('INSERT INTO seed_runs (filename) VALUES (:filename)');
        $insert->execute(['filename' => $filename]);
        if ($pdo->inTransaction()) {
            $pdo->commit();
        }
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, "[error] {$filename}: {$exception->getMessage()}" . PHP_EOL);
        exit(1);
    }
}

echo 'Seeds concluídos.' . PHP_EOL;
