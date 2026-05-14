<?php
require_once "app/config/database.php";

class FeedbackModel
{
    private $conn;

    public function __construct()
    {
        $this->conn = (new Database())->getConnection();
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS user_feedback (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                rating TINYINT NOT NULL DEFAULT 5,
                category VARCHAR(80) NOT NULL DEFAULT 'general',
                title VARCHAR(180) NOT NULL,
                message TEXT NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT 'new',
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_feedback_user_created (user_id, created_at),
                INDEX idx_feedback_status_created (status, created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
    }

    public function create(int $userId, int $rating, string $category, string $title, string $message): bool
    {
        $rating = max(1, min(5, $rating));
        $category = trim($category) ?: 'general';
        $title = trim($title);
        $message = trim($message);

        $stmt = $this->conn->prepare("
            INSERT INTO user_feedback (user_id, rating, category, title, message)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisss", $userId, $rating, $category, $title, $message);
        return $stmt->execute();
    }

    public function getByUser(int $userId): array
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM user_feedback
            WHERE user_id = ?
            ORDER BY created_at DESC
            LIMIT 20
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();

        $rows = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function getAll(string $status = ''): array
    {
        $sql = "
            SELECT f.*, u.name, u.email
            FROM user_feedback f
            LEFT JOIN users u ON u.id = f.user_id
        ";
        $params = [];
        $types = '';

        if ($status !== '') {
            $sql .= " WHERE f.status = ?";
            $params[] = $status;
            $types .= 's';
        }

        $sql .= " ORDER BY f.created_at DESC";
        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();

        $rows = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function countNew(): int
    {
        $result = $this->conn->query("SELECT COUNT(*) AS total FROM user_feedback WHERE status = 'new'");
        return intval($result->fetch_assoc()['total'] ?? 0);
    }

    public function updateStatus(int $id, string $status): bool
    {
        $allowed = ['new', 'reviewed', 'resolved'];
        if (!in_array($status, $allowed, true)) {
            return false;
        }

        $stmt = $this->conn->prepare("UPDATE user_feedback SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }
}
