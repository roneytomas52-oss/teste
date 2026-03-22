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

        $driver = $this->config['driver'] ?? 'mysql';
        $dsn = match ($driver) {
            'mysql' => sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['charset'] ?? 'utf8mb4'
            ),
            'sqlite' => sprintf('sqlite:%s', $this->config['database']),
            default => sprintf(
                'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
                $this->config['host'],
                $this->config['port'],
                $this->config['database'],
                $this->config['sslmode'] ?? 'prefer'
            ),
        };

        try {
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $username = $driver === 'sqlite' ? null : $this->config['username'];
            $password = $driver === 'sqlite' ? null : $this->config['password'];

            $this->pdo = new PDO($dsn, $username, $password, $options);
        } catch (PDOException $exception) {
            throw new ApiException(500, 'DATABASE_CONNECTION_FAILED', 'Nao foi possivel conectar ao banco de dados.', [
                'driver' => $driver,
                'reason' => $exception->getMessage(),
            ]);
        }

        return $this->pdo;
    }
}
