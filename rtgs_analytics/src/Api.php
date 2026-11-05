<?php
// src/Api.php

class Api
{
    private $pdo;
    private $logger;

    public function __construct($pdo, $logger = null)
    {
        $this->pdo = $pdo;
        $this->logger = $logger;
    }

    public function loadData($data, $userId)
    {
        try {
            $this->pdo->beginTransaction();

            // 0. Create Audit Session
            $stmt = $this->pdo->prepare("INSERT INTO audit_sessions (user_id, description, created_at) VALUES (?, ?, NOW())");
            $desc = "Audit Import - " . date('d/m/Y H:i');
            $stmt->execute([$userId, $desc]);
            $sessionId = $this->pdo->lastInsertId();

            if ($this->logger) {
                $this->logger->logEtl($userId, 'STARTED', "Started ETL Session #$sessionId");
            }

            // 1. Logs
            $countLogs = 0;
            if (!empty($data['logs'])) {
                $stmt = $this->pdo->prepare("INSERT INTO rtgs_message_log (log_datetime, direction, status, msg_type, reference, raw_content, session_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                foreach ($data['logs'] as $l) {
                    $dt = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $l['dt'] ?? 'now')));
                    $stmt->execute([$dt, $l['dir'], $l['status'], $l['type'], $l['ref'], $l['raw'], $sessionId]);
                    $countLogs++;
                }
            }

            // 2. Transactions
            $countTrans = 0;
            if (!empty($data['trans'])) {
                $stmt = $this->pdo->prepare("INSERT INTO sprtgs_transactions (chrono, reference, amount, trans_type, raw_content, session_id) VALUES (?, ?, ?, ?, ?, ?)");
                $seenChronos = [];
                foreach ($data['trans'] as $t) {
                    if (isset($seenChronos[$t['chrono']]))
                        continue;
                    $seenChronos[$t['chrono']] = true;
                    $stmt->execute([$t['chrono'], $t['ref'], $t['amount'] ?? 0, $t['type'], $t['raw'], $sessionId]);
                    $countTrans++;
                }
            }

            $this->pdo->commit();

            if ($this->logger) {
                $this->logger->logEtl($userId, 'SUCCESS', "Completed ETL Session #$sessionId. Imported $countLogs logs and $countTrans transactions.", $countLogs + $countTrans);
            }

            return [
                'status' => 'ok',
                'count_logs' => $countLogs,
                'count_trans' => $countTrans,
                'session_id' => $sessionId
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            if ($this->logger) {
                $this->logger->logEtl($userId, 'FAILED', "ETL Failed: " . $e->getMessage());
            }
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}
?>