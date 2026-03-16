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

function storage_url(string $folder, ?string $file): string
{
    if (!$file) {
        return '';
    }
    return sixammart_url('storage/app/public/' . trim($folder, '/') . '/' . ltrim($file, '/'));
}

function get_business_setting(string $key, mixed $default = null): mixed
{
    try {
        $stmt = db()->prepare('SELECT value FROM business_settings WHERE `key` = :key LIMIT 1');
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
        $stmt = db()->prepare('SELECT value FROM data_settings WHERE `type` = :type AND `key` = :key LIMIT 1');
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

function get_data_settings_by_type(string $type): array
{
    try {
        $stmt = db()->prepare('SELECT `key`, `value` FROM data_settings WHERE `type` = :type');
        $stmt->execute(['type' => $type]);
        $rows = $stmt->fetchAll();
        $mapped = [];
        foreach ($rows as $row) {
            $decoded = json_decode((string) $row['value'], true);
            $mapped[$row['key']] = json_last_error() === JSON_ERROR_NONE ? $decoded : $row['value'];
        }
        return $mapped;
    } catch (Throwable) {
        return [];
    }
}

function get_active_modules(int $limit = 5): array
{
    try {
        $stmt = db()->prepare('SELECT id, module_name, icon FROM modules WHERE status = 1 ORDER BY id ASC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['icon_full_url'] = storage_url('module', $row['icon'] ?? '');
        }
        unset($row);

        return $rows;
    } catch (Throwable) {
        return [];
    }
}

function get_active_admin_features(int $limit = 3): array
{
    try {
        $stmt = db()->prepare('SELECT id, title, sub_title, image FROM admin_features WHERE status = 1 ORDER BY id ASC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['image_full_url'] = storage_url('admin_feature', $row['image'] ?? '');
        }
        unset($row);

        return $rows;
    } catch (Throwable) {
        return [];
    }
}

function get_social_media(): array
{
    try {
        $stmt = db()->query('SELECT name, link FROM social_media WHERE active = 1 ORDER BY id ASC');
        return $stmt->fetchAll();
    } catch (Throwable) {
        return [];
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
