<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'todo';
$port = getenv('DB_PORT') ?: 3306;

$conn = new mysqli($host, $user, $password, $dbname, (int) $port);

if ($conn->connect_errno) {
    error_log('DB connection failed: ' . $conn->connect_error);
    http_response_code(500);
    die('Database connection error.');
}
