<?php

declare(strict_types=1);

$apiRoot = dirname(__DIR__);

require_once $apiRoot . '/bootstrap/autoload.php';
require_once $apiRoot . '/bootstrap/env.php';

fox_api_load_env($apiRoot);

$containerFactory = require $apiRoot . '/bootstrap/container.php';
$container = $containerFactory($apiRoot);
$pdo = $container->get(\FoxPlatform\Api\Infrastructure\Persistence\DatabaseConnection::class)->pdo();

$pdo->exec(
    'CREATE TABLE IF NOT EXISTS schema_migrations (
        filename VARCHAR(255) PRIMARY KEY,
        executed_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
    )'
);

$migrationsPath = $apiRoot . '/database/migrations/*.sql';
$files = glob($migrationsPath) ?: [];
sort($files);

foreach ($files as $file) {
    $filename = basename($file);
    $statement = $pdo->prepare('SELECT 1 FROM schema_migrations WHERE filename = :filename');
    $statement->execute(['filename' => $filename]);

    if ($statement->fetchColumn()) {
        echo "[skip] {$filename}" . PHP_EOL;
        continue;
    }

    echo "[run] {$filename}" . PHP_EOL;
    $sql = file_get_contents($file);

    $pdo->beginTransaction();
    try {
        $pdo->exec($sql);
        $insert = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (:filename)');
        $insert->execute(['filename' => $filename]);
        $pdo->commit();
    } catch (Throwable $exception) {
        $pdo->rollBack();
        fwrite(STDERR, "[error] {$filename}: {$exception->getMessage()}" . PHP_EOL);
        exit(1);
    }
}

echo 'Migrations concluídas.' . PHP_EOL;
