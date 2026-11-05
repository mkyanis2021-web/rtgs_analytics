<?php
// templates/nexus.php
// Palantir-style Investigative Interface
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTGS Nexus | Operational Intelligence</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/nexus.css">
</head>

<body class="nexus-mode">
    <div class="sidebar">
        <div class="nexus-logo">RTGS NEXUS</div>

        <div class="filter-group">
            <label>Swift Type</label>
            <select id="swiftFilter" class="nexus-select">
                <option value="">All Types</option>
                <option value="103">MT103 (Payments)</option>
                <option value="202">MT202 (Bank Transfer)</option>
                <option value="910">MT910 (Credit Advice)</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Status</label>
            <select id="statusFilter" class="nexus-select">
                <option value="">All Statuses</option>
                <option value="ACK">ACK</option>
                <option value="NAK">NAK</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Match Confidence (Min %)</label>
            <input type="range" min="0" max="100" value="0" id="confidenceRange" class="nexus-range"
                oninput="document.getElementById('confValue').innerText = this.value + '%'">
            <div style="text-align:right; font-size:0.8rem; color:#8b949e" id="confValue">0%</div>
        </div>

        <div class="filter-group">
            <label>Date Filter</label>
            <input type="date" id="dateFilter" class="nexus-select">
        </div>

        <button onclick="applyFilters()" class="btn-nexus">Apply Analytics</button>
        <a href="index.php?action=dashboard"
            style="text-align:center; color:#8b949e; text-decoration:none; font-size:0.8rem; margin-top:20px;">&larr;
            Return to Dashboard</a>
    </div>

    <main class="content">
        <header class="stats-ribbon">
            <div class="stat-card">Total Logs: <span id="totalLogs">--</span></div>
            <div class="stat-card">Matched Links: <span id="matchedCount">--</span></div>
            <div class="stat-card warning">Orphans: <span id="orphanCount">--</span></div>
        </header>

        <section class="data-grid-container">
            <table class="nexus-table" id="rtgsGrid">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Log Ref</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Match Level</th>
                        <th>Confidence</th>
                        <th>Transac Amount</th>
                    </tr>
                </thead>
                <tbody id="gridBody">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </section>
    </main>
    <script src="public/js/nexus.js"></script>
</body>

</html>