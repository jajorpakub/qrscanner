<?php

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = getenv('DB_PORT') ?: 3306;
$dbName = getenv('DB_NAME') ?: 'qrscanner';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') ?: '';

return [
    'host' => $dbHost,
    'port' => (int)$dbPort,
    'dbname' => $dbName,
    'user' => $dbUser,
    'password' => $dbPassword,
    'driver' => 'pdo_mysql',
    'charset' => 'utf8mb4',
];
