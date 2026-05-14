<?php
require_once "app/config/database.php";

class ChatModel
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
            CREATE TABLE IF NOT EXISTS chat_conversations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(160) NOT NULL DEFAULT 'Hội thoại mới',
                is_pinned TINYINT(1) NOT NULL DEFAULT 0,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_chat_conversations_user_updated (user_id, updated_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        $conversationColumns = [];
        $conversationResult = $this->conn->query("SHOW COLUMNS FROM chat_conversations");
        while ($row = $conversationResult->fetch_assoc()) {
            $conversationColumns[$row['Field']] = true;
        }

        if (empty($conversationColumns['is_pinned'])) {
            $this->conn->query("ALTER TABLE chat_conversations ADD COLUMN is_pinned TINYINT(1) NOT NULL DEFAULT 0 AFTER title");
        }

        $columns = [];
        $result = $this->conn->query("SHOW COLUMNS FROM chat_messages");
        while ($row = $result->fetch_assoc()) {
            $columns[$row['Field']] = true;
        }

        if (empty($columns['conversation_id'])) {
            $this->conn->query("ALTER TABLE chat_messages ADD COLUMN conversation_id INT NULL AFTER user_id");
            $this->conn->query("ALTER TABLE chat_messages ADD INDEX idx_chat_messages_conversation (conversation_id)");
        }

        $this->migrateLegacyMessages();
    }

    private function migrateLegacyMessages(): void
    {
        $this->conn->query("
            INSERT INTO chat_conversations (user_id, title, created_at, updated_at)
            SELECT m.user_id, 'Lịch sử cũ', MIN(m.created_at), MAX(m.created_at)
            FROM chat_messages m
            WHERE m.conversation_id IS NULL
            GROUP BY m.user_id
        ");

        $this->conn->query("
            UPDATE chat_messages m
            JOIN (
                SELECT user_id, MAX(id) AS conversation_id
                FROM chat_conversations
                WHERE title = 'Lịch sử cũ'
                GROUP BY user_id
            ) c ON c.user_id = m.user_id
            SET m.conversation_id = c.conversation_id
            WHERE m.conversation_id IS NULL
        ");
    }

    public function createConversation(int $userId, string $title = 'Hội thoại mới'): int
    {
        $stmt = $this->conn->prepare("INSERT INTO chat_conversations (user_id, title) VALUES (?, ?)");
        $stmt->bind_param("is", $userId, $title);
        $stmt->execute();
        return (int)$stmt->insert_id;
    }

    public function userOwnsConversation(int $userId, int $conversationId): bool
    {
        $stmt = $this->conn->prepare("SELECT id FROM chat_conversations WHERE id = ? AND user_id = ? LIMIT 1");
        $stmt->bind_param("ii", $conversationId, $userId);
        $stmt->execute();
        return (bool)$stmt->get_result()->fetch_assoc();
    }

    public function getConversations(int $userId): array
    {
        $stmt = $this->conn->prepare("
            SELECT c.id, c.title, c.is_pinned, c.created_at, c.updated_at, COUNT(m.id) AS message_count
            FROM chat_conversations c
            LEFT JOIN chat_messages m ON m.conversation_id = c.id
            WHERE c.user_id = ?
            GROUP BY c.id
            HAVING message_count > 0
            ORDER BY c.is_pinned DESC, c.updated_at DESC
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

    public function getByConversation(int $userId, int $conversationId): array
    {
        $stmt = $this->conn->prepare("
            SELECT *
            FROM chat_messages
            WHERE user_id = ? AND conversation_id = ?
            ORDER BY created_at ASC, id ASC
        ");
        $stmt->bind_param("ii", $userId, $conversationId);
        $stmt->execute();

        $rows = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function save($userId, $role, $message, $image = null, ?int $conversationId = null)
    {
        $sql = "INSERT INTO chat_messages (user_id, conversation_id, role, message, image)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iisss", $userId, $conversationId, $role, $message, $image);
        $ok = $stmt->execute();

        if ($ok && $conversationId) {
            $this->touchConversation($conversationId);
            if ($role === 'user') {
                $this->updateTitleFromFirstMessage($conversationId, $message);
            }
        }

        return $ok;
    }

    public function getRecent($userId, ?int $conversationId = null, $limit = 10)
    {
        if ($conversationId) {
            $stmt = $this->conn->prepare(
                "SELECT role, message FROM chat_messages
                 WHERE user_id = ? AND conversation_id = ?
                 ORDER BY created_at DESC, id DESC LIMIT ?"
            );
            $stmt->bind_param("iii", $userId, $conversationId, $limit);
        } else {
            $stmt = $this->conn->prepare(
                "SELECT role, message FROM chat_messages
                 WHERE user_id = ? ORDER BY created_at DESC, id DESC LIMIT ?"
            );
            $stmt->bind_param("ii", $userId, $limit);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($r = $res->fetch_assoc()) {
            $data[] = $r;
        }

        return array_reverse($data);
    }

    public function deleteByUser($userId)
    {
        $stmt = $this->conn->prepare("DELETE FROM chat_messages WHERE user_id = ?");
        $stmt->bind_param("i", $userId);
        return $stmt->execute();
    }

    public function setPinned(int $userId, int $conversationId, bool $pinned): bool
    {
        $value = $pinned ? 1 : 0;
        $stmt = $this->conn->prepare("UPDATE chat_conversations SET is_pinned = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $value, $conversationId, $userId);
        return $stmt->execute();
    }

    public function countPinned(int $userId): int
    {
        $stmt = $this->conn->prepare("SELECT COUNT(*) AS total FROM chat_conversations WHERE user_id = ? AND is_pinned = 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        return (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    }

    public function deleteConversation(int $userId, int $conversationId): bool
    {
        $messageStmt = $this->conn->prepare("DELETE FROM chat_messages WHERE conversation_id = ? AND user_id = ?");
        $messageStmt->bind_param("ii", $conversationId, $userId);
        $messageStmt->execute();

        $conversationStmt = $this->conn->prepare("DELETE FROM chat_conversations WHERE id = ? AND user_id = ?");
        $conversationStmt->bind_param("ii", $conversationId, $userId);
        return $conversationStmt->execute();
    }

    private function touchConversation(int $conversationId): void
    {
        $stmt = $this->conn->prepare("UPDATE chat_conversations SET updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
    }

    private function updateTitleFromFirstMessage(int $conversationId, string $message): void
    {
        $stmt = $this->conn->prepare("SELECT title FROM chat_conversations WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row || $row['title'] !== 'Hội thoại mới') {
            return;
        }

        $title = trim(preg_replace('/\s+/', ' ', $message));
        if (mb_strlen($title, 'UTF-8') > 48) {
            $title = mb_substr($title, 0, 48, 'UTF-8') . '...';
        }
        if ($title === '' || str_starts_with($title, '[')) {
            $title = 'Phân tích dinh dưỡng';
        }

        $update = $this->conn->prepare("UPDATE chat_conversations SET title = ? WHERE id = ?");
        $update->bind_param("si", $title, $conversationId);
        $update->execute();
    }
}
