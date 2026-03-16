<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            env('DB_HOST', '127.0.0.1'),
            env('DB_PORT', '3306'),
            env('DB_DATABASE', ''),
            env('DB_CHARSET', 'utf8mb4')
        );

        $pdo = new PDO($dsn, env('DB_USERNAME', ''), env('DB_PASSWORD', ''), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    return $pdo;
}

function get_business_setting(string $key, mixed $default = null): mixed
{
    try {
        $sql = 'SELECT value FROM business_settings WHERE `key` = :key LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute(['key' => $key]);
        $row = $stmt->fetch();
        if (!$row) {
            return $default;
        }

        $value = $row['value'];
        $decoded = json_decode((string) $value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    } catch (Throwable) {
        return $default;
    }
}

function get_data_setting(string $type, string $key, mixed $default = null): mixed
{
    try {
        $sql = 'SELECT value FROM data_settings WHERE `type` = :type AND `key` = :key LIMIT 1';
        $stmt = db()->prepare($sql);
        $stmt->execute(['type' => $type, 'key' => $key]);
        $row = $stmt->fetch();
        if (!$row) {
            return $default;
        }

        $value = $row['value'];
        $decoded = json_decode((string) $value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    } catch (Throwable) {
        return $default;
    }
}

function save_contact(string $name, string $email, string $message): void
{
    $sql = 'INSERT INTO contacts (name, email, subject, message, seen, reply, created_at, updated_at)
            VALUES (:name, :email, :subject, :message, 0, NULL, NOW(), NOW())';

    $stmt = db()->prepare($sql);
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'subject' => 'Contato via landing standalone',
        'message' => $message,
    ]);
}
