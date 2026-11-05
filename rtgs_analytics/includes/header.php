<!-- templates/header.php -->
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTGS Analytics</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2563eb;
            --bg: #f8fafc;
            --surface: #ffffff;
            --text: #1e293b;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: var(--surface);
            border-bottom: 1px solid #e2e8f0;
            padding: 0 40px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-logo {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-link {
            text-decoration: none;
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
            transition: 0.2s;
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary);
        }

        .user-menu {
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logout-btn {
            color: #dc2626;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid #fee2e2;
            padding: 6px 12px;
            border-radius: 6px;
        }

        .logout-btn:hover {
            background: #fee2e2;
        }

        /* Main Container */
        .main-container {
            flex: 1;
            padding: 40px;
            max-width: 1440px;
            margin: 0 auto;
            width: 100%;
            box-sizing: border-box;
        }

        /* Common Utils */
        .card {
            background: var(--surface);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .grid-kpi {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .kpi-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .kpi-val {
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
        }

        .kpi-label {
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
            margin-top: 5px;
        }

        .filters {
            display: flex;
            gap: 15px;
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
            align-items: flex-end;
        }

        .form-g {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .form-g label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
        }

        .form-control {
            padding: 8px 12px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-family: inherit;
        }

        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            color: white;
            background: var(--primary);
        }

        .btn:hover {
            background: #1d4ed8;
        }

        /* Tag Styles */
        .tag {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            display: inline-block;
        }

        .tag-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .tag-green {
            background: #dcfce7;
            color: #166534;
        }

        .tag-red {
            background: #fee2e2;
            color: #991b1b;
        }

        .tag-orange {
            background: #ffedd5;
            color: #9a3412;
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <div class="nav-logo">
            <span>📊</span> RTGS Analytics
        </div>
        <div class="nav-links">
            <a href="index.php?action=dashboard"
                class="nav-link <?= $action == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
            <?php if ($auth->hasRole('Admin')): ?>
                <a href="index.php?action=etl" class="nav-link <?= $action == 'etl' ? 'active' : '' ?>">🛠️ ETL Pipeline</a>
                <a href="index.php?action=history" class="nav-link <?= $action == 'history' ? 'active' : '' ?>">📜
                    Historique</a>
                <a href="index.php?action=activity_logs"
                    class="nav-link <?= $action == 'activity_logs' ? 'active' : '' ?>">📜 Logs</a>
                <a href="index.php?action=admin_users" class="nav-link <?= $action == 'admin_users' ? 'active' : '' ?>">👤
                    Utilisateurs</a>
            <?php endif; ?>
        </div>
        <div class="user-menu">
            <span>👤
                <?= htmlspecialchars($_SESSION['username']) ?> <small>(
                    <?= htmlspecialchars($_SESSION['role']) ?>)
                </small>
            </span>
            <a href="index.php?action=logout" class="logout-btn">Déconnexion</a>
        </div>
    </nav>
    <div class="main-container">