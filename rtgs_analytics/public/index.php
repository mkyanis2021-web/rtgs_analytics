<?php
// public/index.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Dashboard.php';
require_once __DIR__ . '/../src/Api.php';
require_once __DIR__ . '/../src/Logger.php'; // NEW

$logger = new Logger($pdo); // NEW
$auth = new Auth($pdo, $logger); // UPDATED
$dashboard = new Dashboard($pdo);
$api = new Api($pdo, $logger); // UPDATED
$action = $_GET['action'] ?? 'dashboard';

// Handle Login POST
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($auth->login($username, $password)) {
        header('Location: index.php?action=dashboard');
        exit;
    } else {
        $error = "Identifiants invalides";
        require __DIR__ . '/../templates/login.php';
        exit;
    }
}

// Handle Logout
if ($action === 'logout') {
    $auth->logout();
    header('Location: index.php?action=login');
    exit;
}

// Public Routes (only Login)
if (!$auth->isLoggedIn()) {
    require __DIR__ . '/../templates/login.php';
    exit;
}

// Protected Routes
$user = $auth->getUser();

switch ($action) {
    case 'dashboard':
        $sessionId = $_GET['session_id'] ?? null;

        // Fetch Sessions list for the UI selector
        $sessions = $dashboard->getAuditSessions();

        // DON'T auto-select a session - let JavaScript load from IndexedDB
        // This allows the pipeline data to be displayed instead of old database data
        // if (!$sessionId && !empty($sessions)) {
        //     $sessionId = $sessions[0]['id'];
        // }

        // Load saved filters if a session is selected
        $savedFilters = [];
        if ($sessionId) {
            $stmt = $pdo->prepare("SELECT filters_json FROM audit_sessions WHERE id = ?");
            $stmt->execute([$sessionId]);
            $session = $stmt->fetch();
            if ($session && $session['filters_json']) {
                $savedFilters = json_decode($session['filters_json'], true) ?: [];
            }
        }

        $kpis = $dashboard->getKPIs($sessionId);
        // Stats
        $volParams = $dashboard->getVolumeOverTime($sessionId);
        $topBics = $dashboard->getTopBICs($sessionId);
        $typeStats = $dashboard->getMsgTypeStats($sessionId);
        $topSenders = $dashboard->getTopSenders($sessionId);
        $topBenefs = $dashboard->getTopBeneficiaries($sessionId);

        $filterOpts = $dashboard->getFilterOptions();

        // Merge filters: URL params > saved filters > defaults
        $filters = [
            'start' => $_GET['start'] ?? $savedFilters['start'] ?? '',
            'end' => $_GET['end'] ?? $savedFilters['end'] ?? '',
            'type' => $_GET['type'] ?? $savedFilters['type'] ?? '',
            'min_amount' => $_GET['min_amount'] ?? $savedFilters['min_amount'] ?? '',
            'max_amount' => $_GET['max_amount'] ?? $savedFilters['max_amount'] ?? '',
            'sender' => $_GET['sender'] ?? $savedFilters['sender'] ?? '',
            'beneficiary' => $_GET['beneficiary'] ?? $savedFilters['beneficiary'] ?? '',
            'keyword' => $_GET['keyword'] ?? $savedFilters['keyword'] ?? '',
            'swift_status' => $_GET['status'] ?? $savedFilters['swift_status'] ?? '',
            'session_id' => $sessionId
        ];
        $searchResults = $dashboard->searchTransactions($filters);

        require __DIR__ . '/../templates/dashboard.php';
        break;

    case 'import':
        if ($user['role'] !== 'Admin') {
            die("Accès réservé aux administrateurs.");
        }
        // ... (Keep existing import logic if needed, or deprecate)
        // For brevity not showing full import logic here as it's less used now
        require __DIR__ . '/../templates/import.php';
        break;

    case 'etl':
        if ($user['role'] !== 'Admin') {
            die("Accès refusé.");
        }
        require __DIR__ . '/../templates/etl.php';
        break;

    case 'save_session':
        // Ensure strictly JSON output, no PHP warnings
        ini_set('display_errors', 0);
        header('Content-Type: application/json');

        if (!$auth->isLoggedIn()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = $_SESSION['user_id'];
            $sourceSessionId = $input['session_id'] ?? null;
            $name = $input['name'] ?? 'Saved Session';

            // Filters
            $filters = [
                'session_id' => $sourceSessionId, // Implicit filter
                'keyword' => $input['keyword'] ?? '',
                'start' => $input['start'] ?? '',
                'end' => $input['end'] ?? '',
                'min_amount' => $input['min_amount'] ?? '',
                'max_amount' => $input['max_amount'] ?? '',
                'sender' => $input['sender'] ?? '',
                'beneficiary' => $input['beneficiary'] ?? '',
                'type' => $input['type'] ?? '',
                'swift_status' => $input['swift_status'] ?? ''
            ];

            $result = $dashboard->saveFilteredSession($userId, $sourceSessionId, $filters, $name);
            echo json_encode($result);
            exit;
        }
        break;

    case 'load_sql':
        // Load SQL data from pipeline into database
        ini_set('display_errors', 0);
        header('Content-Type: application/json');

        if (!$auth->isLoggedIn()) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            $sqlContent = $input['sql'] ?? '';

            if (empty($sqlContent)) {
                echo json_encode(['status' => 'error', 'message' => 'No SQL content provided']);
                exit;
            }

            try {
                // Delete existing pipeline data (session_id IS NULL)
                $pdo->exec("DELETE FROM rtgs_message_log WHERE session_id IS NULL");
                $pdo->exec("DELETE FROM sprtgs_transactions WHERE session_id IS NULL");

                // Force session_id to NULL by replacing numeric values at end of INSERT VALUES
                $sqlContent = preg_replace('/,\s*\d+\s*\)/i', ', NULL)', $sqlContent);

                // Execute the SQL directly
                // Split by semicolon and execute each statement
                $statements = array_filter(array_map('trim', explode(';', $sqlContent)));
                $pdo->beginTransaction();

                foreach ($statements as $stmt) {
                    if (!empty($stmt)) {
                        $pdo->exec($stmt);
                    }
                }

                $pdo->commit();
                echo json_encode(['status' => 'success', 'message' => 'Data loaded successfully']);
            } catch (Exception $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
            exit;
        }
        break;

    // NEW: Activity Logs
    case 'activity_logs':
        if ($user['role'] !== 'Admin') {
            die("Accès réservé aux administrateurs.");
        }
        $userLogs = $logger->getUserLogs();
        $etlLogs = $logger->getEtlLogs();
        require __DIR__ . '/../templates/activity_logs.php';
        break;


    // NEW: Session History
    case 'history':
        $sessions = $dashboard->getAuditSessions();
        require __DIR__ . '/../templates/history.php';
        break;

    case 'admin_users':
        if ($user['role'] !== 'Admin') {
            die("Accès réservé aux administrateurs.");
        }

        // Handle Creation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $uName = $_POST['username'] ?? '';
            $uPass = $_POST['password'] ?? '';
            $uRole = $_POST['role_id'] ?? '';

            if ($uName && $uPass && $uRole) {
                try {
                    if ($auth->createUser($uName, $uPass, $uRole)) {
                        $msg = "Utilisateur créé avec succès !";
                    } else {
                        $err = "Erreur lors de la création.";
                    }
                } catch (Exception $e) {
                    $err = "Erreur (probablement doublon): " . $e->getMessage();
                }
            } else {
                $err = "Tous les champs sont requis.";
            }
        }

        // Fetch Data for View
        $roles = $auth->getRoles();
        $usersList = $auth->getAllUsers();

        require __DIR__ . '/../templates/admin_users.php';
        break;

    case 'api_upload':
        header('Content-Type: application/json');
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            echo json_encode(['status' => 'error', 'message' => 'No data']);
            exit;
        }
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        echo json_encode($api->loadData($input, $_SESSION['user_id']));
        exit;
        break;

    case 'nexus':
        // Palantir-style Investigative Module
        require __DIR__ . '/../templates/nexus.php';
        break;

    case 'nexus_api':
        // API for Nexus
        require_once __DIR__ . '/../src/Nexus.php';
        $nexus = new Nexus($pdo);
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $filters = [
                'swift_type' => $_POST['swift_type'] ?? '',
                'min_confidence' => $_POST['min_confidence'] ?? '',
                // Add other potential filters
            ];
            echo json_encode($nexus->fetchTransactions($filters));
        } else {
            // GET stats
            echo json_encode($nexus->getStats());
        }
        exit;
        break;

    default:
        // CHANGED: Default is now ETL for Admins, or Dashboard for others
        if ($auth->hasRole('Admin')) {
            header('Location: index.php?action=etl');
        } else {
            header('Location: index.php?action=dashboard');
        }
        break;
}
?>