<?php

declare(strict_types=1);

if ($argc !== 3 || ! in_array($argv[1], ['create', 'verify'], true)) {
    fwrite(STDERR, "Usage: php sqlite-backup.php create|verify PATH\n");
    exit(64);
}

$operation = $argv[1];
$path = $argv[2];

if ($operation === 'create') {
    $source = getenv('DB_DATABASE') ?: '/app/database/docker.sqlite';
    if (! is_file($source)) {
        throw new RuntimeException('SQLite source database does not exist.');
    }
    if (file_exists($path)) {
        throw new RuntimeException('Backup target already exists.');
    }

    $pdo = new PDO('sqlite:'.$source, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('VACUUM INTO '.$pdo->quote($path));
}

if (! is_file($path) || filesize($path) === 0) {
    throw new RuntimeException('Backup file is missing or empty.');
}

$pdo = new PDO('sqlite:'.$path, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$integrity = $pdo->query('PRAGMA integrity_check')->fetchColumn();
if ($integrity !== 'ok') {
    throw new RuntimeException('SQLite integrity check failed.');
}

$tables = (int) $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type = 'table'")->fetchColumn();
$migrations = (int) $pdo->query('SELECT COUNT(*) FROM migrations')->fetchColumn();
if ($tables === 0 || $migrations === 0) {
    throw new RuntimeException('Restored database does not contain the expected schema.');
}

printf("SQLite %s verified: %d bytes, %d tables, %d migrations.\n", $operation, filesize($path), $tables, $migrations);
