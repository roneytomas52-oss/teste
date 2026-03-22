<?php

declare(strict_types=1);

namespace FoxPlatform\Api\Scripts;

final class SqlDialectTransformer
{
    public static function transform(string $sql, string $driver): string
    {
        if ($driver !== 'mysql') {
            return $sql;
        }

        $sql = preg_replace('/^\s*CREATE EXTENSION IF NOT EXISTS pgcrypto;\s*/mi', '', $sql) ?? $sql;
        $sql = str_replace('TIMESTAMPTZ', 'DATETIME', $sql);
        $sql = str_replace('UUID', 'CHAR(36)', $sql);
        $sql = str_replace('JSONB', 'LONGTEXT', $sql);
        $sql = str_replace('INET', 'VARCHAR(45)', $sql);
        $sql = str_replace('BOOLEAN', 'TINYINT(1)', $sql);
        $sql = preg_replace('/refresh_token_hash\s+TEXT/i', 'refresh_token_hash VARCHAR(255)', $sql) ?? $sql;
        $sql = preg_replace('/token_hash\s+TEXT/i', 'token_hash VARCHAR(255)', $sql) ?? $sql;
        $sql = preg_replace('/DEFAULT\s+gen_random_uuid\(\)/i', 'DEFAULT (UUID())', $sql) ?? $sql;
        $sql = str_ireplace('gen_random_uuid()', 'UUID()', $sql);
        $sql = preg_replace('/DEFAULT\s+UUID\(\)/i', 'DEFAULT (UUID())', $sql) ?? $sql;
        $sql = str_replace("'[]'::jsonb", "'[]'", $sql);
        $sql = str_replace("'{}'::jsonb", "'{}'", $sql);
        $sql = preg_replace('/::jsonb\b/', '', $sql) ?? $sql;
        $sql = preg_replace('/::date\b/', '', $sql) ?? $sql;
        $sql = preg_replace('/DEFAULT NOW\(\)/i', 'DEFAULT CURRENT_TIMESTAMP', $sql) ?? $sql;
        $sql = preg_replace('/LONGTEXT\s+NOT\s+NULL\s+DEFAULT\s+\'\[\]\'/i', 'LONGTEXT NOT NULL', $sql) ?? $sql;
        $sql = preg_replace('/LONGTEXT\s+NOT\s+NULL\s+DEFAULT\s+\'\{\}\'/i', 'LONGTEXT NOT NULL', $sql) ?? $sql;
        $sql = preg_replace('/\bADD\s+COLUMN\s+IF\s+NOT\s+EXISTS\b/i', 'ADD COLUMN', $sql) ?? $sql;
        $sql = preg_replace('/\bCREATE\s+INDEX\s+IF\s+NOT\s+EXISTS\b/i', 'CREATE INDEX', $sql) ?? $sql;
        $sql = preg_replace('/ALTER\s+TABLE\s+[^\;]+DROP\s+CONSTRAINT\s+IF\s+EXISTS\s+[^\;]+;\s*/i', '', $sql) ?? $sql;
        $sql = preg_replace('/(\bCREATE\s+INDEX\b[\s\S]*?\))\s+WHERE\s+[^;]+;/i', '$1;', $sql) ?? $sql;
        $sql = preg_replace_callback(
            '/UPDATE\s+store_team_members\s+stm\s+SET\s+user_id\s*=\s*u\.id\s+FROM\s+users\s+u\s+WHERE\s+stm\.user_id\s+IS\s+NULL\s+AND\s+LOWER\(stm\.email\)\s*=\s*LOWER\(u\.email\);/i',
            static fn (): string => 'UPDATE store_team_members stm INNER JOIN users u ON LOWER(stm.email) = LOWER(u.email) SET stm.user_id = u.id WHERE stm.user_id IS NULL;',
            $sql
        ) ?? $sql;

        $sql = preg_replace_callback(
            "/(NOW\\(\\)|CURRENT_DATE)\\s*([+-])\\s*INTERVAL\\s+'(\\d+)\\s+([a-zA-Z]+)'/i",
            static function (array $matches): string {
                $base = strtoupper($matches[1]);
                $direction = $matches[2];
                $amount = (int) $matches[3];
                $unit = strtoupper(rtrim($matches[4], 's'));
                $fn = $direction === '+' ? 'DATE_ADD' : 'DATE_SUB';

                return sprintf('%s(%s, INTERVAL %d %s)', $fn, $base, $amount, $unit);
            },
            $sql
        ) ?? $sql;

        $sql = preg_replace_callback(
            '/INSERT\s+INTO\s+([^\s(]+)\s*\(([^)]+)\)([\s\S]*?)ON\s+CONFLICT\s*(\([^)]+\))?\s+DO\s+NOTHING;/ims',
            static function (array $matches): string {
                $columns = array_map('trim', explode(',', $matches[2]));
                $firstColumn = trim($columns[0] ?? 'id', " \t\n\r\0\x0B`\"'");

                return sprintf(
                    'INSERT INTO %s (%s)%sON DUPLICATE KEY UPDATE %s = VALUES(%s);',
                    $matches[1],
                    $matches[2],
                    $matches[3],
                    $firstColumn,
                    $firstColumn
                );
            },
            $sql
        ) ?? $sql;

        $sql = preg_replace_callback(
            '/ON CONFLICT\s+\(([^)]+)\)\s+DO UPDATE\s+SET\s+(.*?);/ims',
            static function (array $matches): string {
                $updates = preg_replace('/EXCLUDED\.([a-zA-Z0-9_]+)/', 'VALUES($1)', $matches[2]) ?? $matches[2];
                return "ON DUPLICATE KEY UPDATE {$updates};";
            },
            $sql
        ) ?? $sql;

        return $sql;
    }
}
