<?php
// src/Dashboard.php

class Dashboard
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAuditSessions()
    {
        return $this->pdo->query("SELECT s.*, u.username FROM audit_sessions s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC")->fetchAll();
    }

    public function getKPIs($sessionId = null)
    {
        $where = $sessionId ? "WHERE session_id = $sessionId" : "WHERE session_id IS NULL";

        $totalTransactions = $this->pdo->query("SELECT COUNT(*) FROM sprtgs_transactions $where")->fetchColumn();
        $totalVolume = $this->pdo->query("SELECT COALESCE(SUM(amount), 0) FROM sprtgs_transactions $where")->fetchColumn();
        $avgAmount = $totalTransactions > 0
            ? $this->pdo->query("SELECT AVG(amount) FROM sprtgs_transactions $where")->fetchColumn()
            : 0;

        return [
            'total_transactions' => $totalTransactions,
            'total_volume' => $totalVolume,
            'avg_amount' => $avgAmount
        ];
    }

    public function getVolumeOverTime($sessionId = null)
    {
        $where = $sessionId ? "WHERE session_id = $sessionId" : "WHERE session_id IS NULL";

        $stmt = $this->pdo->query("
            SELECT dco as date, SUM(amount) as volume
            FROM sprtgs_transactions
            $where
            GROUP BY dco
            ORDER BY dco
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTopBICs($sessionId = null)
    {
        $where = $sessionId ? "WHERE session_id = " . intval($sessionId) : "WHERE session_id IS NULL";

        // Revised: Since 'bic_cpa' column is missing in current schema, 
        // we show Top 5 Transactions by Amount instead.
        // Aliasing 'reference' as 'bic' and 'amount' as 'total_amount' to keep frontend compatibility.
        $sql = "SELECT reference as bic, 1 as count, amount as total_amount 
                FROM sprtgs_transactions 
                $where
                ORDER BY amount DESC 
                LIMIT 5";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getMsgTypeStats($sessionId = null)
    {
        $where = $sessionId ? "WHERE session_id = " . intval($sessionId) : "WHERE session_id IS NULL";
        $sql = "SELECT trans_type, COUNT(*) as count 
                FROM sprtgs_transactions $where 
                GROUP BY trans_type";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getTopSenders($sessionId = null)
    {
        $where = $sessionId ? "WHERE session_id = " . intval($sessionId) : "WHERE session_id IS NULL";
        $sql = "SELECT sender, COUNT(*) as count, SUM(amount) as total_amount
                FROM sprtgs_transactions $where
                GROUP BY sender
                ORDER BY total_amount DESC
                LIMIT 5";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function getTopBeneficiaries($sessionId = null)
    {
        $where = $sessionId ? "WHERE session_id = " . intval($sessionId) : "WHERE session_id IS NULL";
        $sql = "SELECT beneficiary, COUNT(*) as count, SUM(amount) as total_amount
                FROM sprtgs_transactions $where
                GROUP BY beneficiary
                ORDER BY total_amount DESC
                LIMIT 5";
        return $this->pdo->query($sql)->fetchAll();
    }

    // --- NEW: Advanced Filters & Search ---

    public function getFilterOptions()
    {
        // Get Min/Max dates
        $dates = $this->pdo->query("SELECT MIN(dco) as min_d, MAX(dco) as max_d FROM sprtgs_transactions")->fetch(PDO::FETCH_ASSOC);

        return [
            'types' => $this->pdo->query("SELECT DISTINCT trans_type FROM sprtgs_transactions ORDER BY trans_type")->fetchAll(PDO::FETCH_COLUMN),
            'log_status' => $this->pdo->query("SELECT DISTINCT status FROM rtgs_message_log ORDER BY status")->fetchAll(PDO::FETCH_COLUMN),
            'min_date' => $dates['min_d'] ?? date('Y-m-d'),
            'max_date' => $dates['max_d'] ?? date('Y-m-d')
        ];
    }

    public function searchTransactions($filters)
    {
        // JOIN with Logs to get SWIFT Status (ACK/NAK) and Direction
        // Removing T.nome/T.nomb as they don't exist. We will parse T.raw_content.
        $sql = "SELECT T.*, L.status as swift_status, L.id as log_id 
                FROM sprtgs_transactions T 
                LEFT JOIN rtgs_message_log L ON T.reference = L.reference 
                WHERE 1=1";
        $params = [];

        // SESSION FILTER
        if (!empty($filters['session_id'])) {
            $sql .= " AND T.session_id = ?";
            $params[] = $filters['session_id'];
        }

        // 1. Keyword Search (Ref or Chrono)
        if (!empty($filters['keyword'])) {
            $sql .= " AND (T.reference LIKE ? OR T.chrono LIKE ?)";
            $params[] = '%' . $filters['keyword'] . '%';
            $params[] = '%' . $filters['keyword'] . '%';
        }

        // 2. Date Range
        if (!empty($filters['start']) && !empty($filters['end'])) {
            $sql .= " AND T.dco BETWEEN ? AND ?";
            $params[] = $filters['start'];
            $params[] = $filters['end'];
        }

        // 3. Min Amount
        if (!empty($filters['min_amount'])) {
            $sql .= " AND T.amount >= ?";
            $params[] = $filters['min_amount'];
        }

        // 4. Message Type (MT103, etc.)
        if (!empty($filters['type'])) {
            $sql .= " AND T.trans_type = ?";
            $params[] = $filters['type'];
        }

        // 5. SWIFT Status Filter (from Log)
        if (!empty($filters['swift_status'])) {
            if ($filters['swift_status'] === 'ORPHANS') {
                $sql .= " AND L.id IS NULL";
            } else {
                $sql .= " AND L.status = ?";
                $params[] = $filters['swift_status'];
            }
        }

        $sql .= " ORDER BY T.id DESC LIMIT 50";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Post-processing to extract Sender/Beneficiary is NO LONGER NEEDED
        // The View `sprtgs_transactions` now provides `sender` and `beneficiary` columns directly.
        // We just ensure defaults if null.
        foreach ($rows as &$row) {
            $row['sender'] = $row['sender'] ?? 'N/A';
            $row['beneficiary'] = $row['beneficiary'] ?? 'N/A';
        }


        return $rows;
    }

    public function saveFilteredSession($userId, $sourceSessionId, $filters, $name)
    {
        try {
            // Simply create a new session with the filters saved
            // No data duplication - filters will be applied client-side when loaded
            $createdAt = date('Y-m-d H:i:s');
            $filtersJson = json_encode($filters);

            $stmt = $this->pdo->prepare("INSERT INTO audit_sessions (user_id, name, filters_json, created_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$userId, $name, $filtersJson, $createdAt]);
            $newSessionId = $this->pdo->lastInsertId();

            return [
                'status' => 'success',
                'message' => 'Session saved successfully',
                'new_session_id' => $newSessionId
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function parseSqlValues($str)
    {
        $res = [];
        $len = strlen($str);
        $cur = '';
        $inQ = false;

        // Remove surrounding parens if present
        $str = trim($str, "()");

        for ($i = 0; $i < strlen($str); $i++) {
            $c = $str[$i];
            if ($c === "'") {
                $inQ = !$inQ;
                continue; // Don't include quotes in output
            }
            if ($c === ',' && !$inQ) {
                $res[] = trim($cur);
                $cur = '';
            } else {
                $cur .= $c;
            }
        }
        $res[] = trim($cur);
        return $res;
    }
}
?>