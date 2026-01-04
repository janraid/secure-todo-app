<?php
use PHPUnit\Framework\TestCase;

final class DatabaseTest extends TestCase
{
    public function testDatabaseConnection(): void
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD');
        $dbname = getenv('DB_NAME') ?: 'todo';
        $port = (int) (getenv('DB_PORT') ?: 3306);

        $conn = new mysqli($host, $user, $password, $dbname, $port);
        $this->assertEquals(0, $conn->connect_errno, 'DB connection failed: ' . $conn->connect_error);
        $conn->close();
    }
}
