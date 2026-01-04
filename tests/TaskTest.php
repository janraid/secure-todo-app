<?php
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    private $conn;

    protected function setUp(): void
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $user = getenv('DB_USER') ?: 'root';
        $password = getenv('DB_PASSWORD') ?: '';
        $dbname = getenv('DB_NAME') ?: 'todo';
        $port = (int) (getenv('DB_PORT') ?: 3306);

        $this->conn = new mysqli($host, $user, $password, $dbname, $port);
        if ($this->conn->connect_errno) {
            $this->markTestSkipped('Database not available: ' . $this->conn->connect_error);
        }
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function testAddAndDeleteTask(): void
    {
        // Create a temporary user
        $username = 'test_user_' . uniqid();
        $passwordHash = hash('sha256', 'password');
        $insUser = $this->conn->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $insUser->bind_param('ss', $username, $passwordHash);
        $ok = $insUser->execute();
        $userId = $insUser->insert_id;
        $insUser->close();

        $this->assertTrue($ok && $userId > 0, 'Failed to create test user');

        // Insert a task for that user
        $taskName = 'Test Task ' . uniqid();
        $taskDetails = 'automated test details';
        $insTask = $this->conn->prepare('INSERT INTO tasks (userid, name, task) VALUES (?, ?, ?)');
        $insTask->bind_param('iss', $userId, $taskName, $taskDetails);
        $okTask = $insTask->execute();
        $taskId = $insTask->insert_id;
        $insTask->close();

        $this->assertTrue($okTask && $taskId > 0, 'Failed to insert task');

        // Verify task exists
        $sel = $this->conn->prepare('SELECT id, name, task FROM tasks WHERE id = ?');
        $sel->bind_param('i', $taskId);
        $sel->execute();
        $res = $sel->get_result();
        $this->assertEquals(1, $res->num_rows, 'Inserted task not found');
        $sel->close();

        // Delete the task
        $del = $this->conn->prepare('DELETE FROM tasks WHERE id = ?');
        $del->bind_param('i', $taskId);
        $delOk = $del->execute();
        $del->close();
        $this->assertTrue($delOk, 'Failed to delete task');

        // Cleanup user
        $delU = $this->conn->prepare('DELETE FROM users WHERE id = ?');
        $delU->bind_param('i', $userId);
        $delU->execute();
        $delU->close();
    }
}
