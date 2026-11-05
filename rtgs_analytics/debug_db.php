<?php
require_once __DIR__ . '/config/db.php';
try {
    $stmt = $pdo->query("SELECT * FROM sprtgs_transactions LIMIT 3");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Count: " . count($rows) . "\n";
    foreach ($rows as $r) {
        print_r($r);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
