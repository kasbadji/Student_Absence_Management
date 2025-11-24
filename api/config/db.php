<?php
$envPath = __DIR__ . '/../../.env';

if (!file_exists($envPath)) {
    throw new Exception(".env file not found.");
}

// Load .env file
$env = parse_ini_file($envPath);
if ($env === false) {
    throw new Exception("Failed to parse .env file.");
}

$config = [
    'host' => $env['DB_HOST'] ?? '127.0.0.1',
    'port' => $env['DB_PORT'] ?? '5432',
    'database' => $env['DB_NAME'] ?? '',
    'username' => $env['DB_USER'] ?? '',
    'password' => $env['DB_PASS'] ?? '',
    'charset' => $env['DB_CHARSET'] ?? 'utf8'
];

$dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['database']};options='--client_encoding={$config['charset']}'";

try {
    $pdo = new PDO($dsn, $config['username'], $config['password']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
