<?php
// src/Nexus.php

class Nexus
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Fetch transactions with advanced filtering context (The "Foundry" layer).
     */
    public function fetchTransactions($filters)
    {
        $sql = "SELECT 
                    l.id, 
                    l.log_datetime,
                    l.msg_type as swift_type, 
                    l.status,
                    l.reference as log_ref,
                    lnk.match_level, 
                    lnk.confidence_score, 
                    t103.amount,
                    t103.reference as trans_ref,
                    t103.chrono
                FROM rtgs_message_log l
                LEFT JOIN rtgs_log_transaction_link lnk ON l.id = lnk.log_id
                LEFT JOIN sprtgs_transactions t103 ON lnk.transaction_chrono = t103.chrono
                WHERE 1=1";

        $params = [];

        // Filter: Swift Type (103, 202, etc.)
        if (!empty($filters['swift_type'])) {
            $sql .= " AND l.msg_type = ?";
            $params[] = $filters['swift_type'];
        }

        // Filter: Status (ACK, NAK)
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }

        // Filter: Confidence Score (Min)
        if (isset($filters['min_confidence']) && $filters['min_confidence'] !== '') {
            $sql .= " AND (lnk.confidence_score >= ? OR lnk.confidence_score IS NULL)";
            $params[] = $filters['min_confidence'];
        }

        // Filter: Date Range
        if (!empty($filters['date_start'])) {
            $sql .= " AND l.log_datetime >= ?";
            $params[] = $filters['date_start'];
        }

        $sql .= " ORDER BY l.log_datetime DESC LIMIT 500"; // Cap results for performance

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats()
    {
        return [
            'total_logs' => $this->pdo->query("SELECT COUNT(*) FROM rtgs_message_log")->fetchColumn(),
            'matched' => $this->pdo->query("SELECT COUNT(*) FROM rtgs_log_transaction_link")->fetchColumn(),
            'orphans' => $this->pdo->query("SELECT COUNT(*) FROM rtgs_message_log WHERE id NOT IN (SELECT log_id FROM rtgs_log_transaction_link)")->fetchColumn()
        ];
    }
}
?>