<?php
// src/Logger.php

class Logger
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function logUserAction($userId, $username, $action, $details = '')
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $stmt = $this->pdo->prepare("INSERT INTO user_logs (user_id, username, action, details, ip_address, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $username, $action, $details, $ip]);
    }

    public function logEtl($userId, $status, $message, $recordsProcessed = 0)
    {
        $stmt = $this->pdo->prepare("INSERT INTO etl_logs (user_id, status, message, records_processed, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $status, $message, $recordsProcessed]);
    }

    public function logSqlImport($userId, $filename, $content, $executed = false)
    {
        $executedAt = $executed ? date('Y-m-d H:i:s') : null;
        $stmt = $this->pdo->prepare("INSERT INTO sql_imports (user_id, filename, file_content, executed_at, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$userId, $filename, $content, $executedAt]);
    }

    public function getUserLogs($limit = 100)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM user_logs ORDER BY created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getEtlLogs($limit = 50)
    {
        // Join with users table to get username if needed, but for now just raw log
        $stmt = $this->pdo->prepare("SELECT l.*, u.username FROM etl_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getSqlHistory($limit = 20)
    {
        $stmt = $this->pdo->prepare("SELECT s.*, u.username FROM sql_imports s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC LIMIT ?");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>