<?php

declare(strict_types=1);

function load_env_file(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $trimmed = trim($line);
        if ($trimmed === '' || str_starts_with($trimmed, '#')) {
            continue;
        }

        [$key, $value] = array_pad(explode('=', $trimmed, 2), 2, '');
        $key = trim($key);
        $value = trim($value);

        if ($key !== '' && getenv($key) === false) {
            putenv("{$key}={$value}");
        }
    }
}

load_env_file(__DIR__ . '/.env');

function env_or_default(string $key, string $default): string
{
    $value = getenv($key);
    return ($value === false || $value === '') ? $default : $value;
}

function get_pdo(): PDO
{
    $driver = env_or_default('DB_DRIVER', 'pgsql');
    $host = env_or_default('DB_HOST', '127.0.0.1');
    $port = env_or_default('DB_PORT', '5432');
    $dbname = env_or_default('DB_NAME', 'app_db');
    $user = env_or_default('DB_USER', 'postgres');
    $pass = env_or_default('DB_PASSWORD', 'postgres');

    if ($driver === 'pgsql') {
        $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
    } else {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    }

    return new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
}
