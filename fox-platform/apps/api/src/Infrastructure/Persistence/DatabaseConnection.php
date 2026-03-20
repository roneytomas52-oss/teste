<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Infrastructure\Persistence;

use PDO;
use PDOException;
use FoxPlatform\Api\Infrastructure\Support\ApiException;

class DatabaseConnection
{
    private ?PDO $pdo = null;

    public function __construct(
        private readonly array $config
    ) {
    }

    public function pdo(): PDO
    {
        if ($this->pdo instanceof PDO) {
            return $this->pdo;
        }

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
            $this->config['host'],
            $this->config['port'],
            $this->config['database'],
            $this->config['sslmode'] ?? 'prefer'
        );

        try {
            $this->pdo = new PDO($dsn, $this->config['username'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $exception) {
            throw new ApiException(500, 'DATABASE_CONNECTION_FAILED', 'Nao foi possivel conectar ao banco de dados.', [
                'driver' => $this->config['driver'] ?? 'pgsql',
                'reason' => $exception->getMessage(),
            ]);
        }

        return $this->pdo;
    }
}
