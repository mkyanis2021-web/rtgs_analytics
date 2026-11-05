<?php
// templates/dashboard.php
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Veuillez vous connecter.");
}
require_once __DIR__ . '/header.php';
?>

<!-- Load Tailwind solely for this page if possible, or assume global. 
     Since header doesn't have it, we load it here. -->
<script src="https://cdn.tailwindcss.com"></script>
<style>
    /* Scoped customs to avoid breaking sticky header if any */
    .drop-zone {
        border: 2px dashed #cbd5e1;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .drop-zone.over {
        border-color: #3b82f6;
        background: #eff6ff;
        transform: scale(1.02);
    }
</style>

<!-- Loading Overlay -->

<div id="loadingOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-2xl p-8 shadow-2xl max-w-md w-full mx-4">
        <div class="flex flex-col items-center">
            <!-- Spinner -->
            <div class="relative w-20 h-20 mb-4">
                <div class="absolute inset-0 border-4 border-slate-200 rounded-full"></div>
                <div class="absolute inset-0 border-4 border-blue-500 rounded-full border-t-transparent animate-spin">
                </div>
            </div>
            <!-- Message -->
            <h3 id="loadingMessage" class="text-xl font-semibold text-slate-800 mb-2">Chargement...</h3>
            <p id="loadingSubtext" class="text-sm text-slate-500 text-center">Veuillez patienter</p>
        </div>
    </div>
</div>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-800">
                    📊 RTGS Analytics
                    <?php if (isset($_GET['session_id']))
                        echo ' | Audit #' . htmlspecialchars($_GET['session_id']); ?>
                </h1>
                <p class="text-slate-500 mt-1">Session: <?= htmlspecialchars($user['username']) ?></p>
            </div>
        </div>
    </div>
    <div class="max-w-[1600px] mx-auto p-6" id="dashboard-app-root">
        <!-- HEADER inside Dashboard Content (Replacing the previous generic header) -->
        <header
            class="flex justify-between items-center mb-8 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div>
                <h1 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <span class="w-2 h-8 bg-blue-600 rounded-full block"></span>
                    RTGS Analytics
                </h1>
                <p class="text-slate-500 text-sm mt-1 ml-4">
                    Session: <?= htmlspecialchars($_SESSION['username']) ?>
                    <?php if (isset($_GET['session_id']))
                        echo ' | Audit #' . htmlspecialchars($_GET['session_id']); ?>
                </p>
                <script>
                                 // ===== LOADING HELPERS =====
                        function showLoading(message = 'Chargement...', subtext = 'Veuillez patienter') {
                            const overlay = document.getElementById('loadingOverlay');
                            const messageEl = document.getElementById('loadingMessage');
                            const subtextEl = document.getElementById('loadingSubtext');

                            messageEl.textContent = message;
                            subtextEl.textContent = subtext;
                            overlay.classList.remove('hidden');
                        }

                        function hideLoading() {
                            const overlay = document.getElementById('loadingOverlay');
                            overlay.classList.add('hidden');
                        }

                        // ===== GLOBAL STATE =====
                    </script>
                </div>
                <div id="statusIndicator"
                    class="hidden px-6 py-4 rounded-xl bg-green-50 border border-green-100 flex items-center gap-3">
                    <div class="h-3 w-3 rounded-full bg-green-500 animate-pulse"></div>
                    <div>
                        <p class="text-sm font-bold text-green-700">Données connectées</p>
                        <p class="text-xs text-green-600">Sync Pipeline ETL: Actif</p>
                    </div>
                </div>
                <div id="dropZone"
                    class="drop-zone px-8 py-4 rounded-xl text-center cursor-pointer group hover:border-blue-400">
                    <div class="flex flex-col items-center gap-2">
                        <svg class="w-8 h-8 text-slate-400 group-hover:text-blue-500 transition" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12">
                            </path>
                        </svg>
                        <span class="text-sm font-medium text-slate-600 group-hover:text-blue-600">Glisser le fichier
                            SQL</span>
                    </div>
                    <input type="file" id="fileInput" class="hidden" accept=".sql">
                </div>
            </header>

            <!-- KPI CARDS -->
            <div id="kpiContainer" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
                <!-- SERVER SIDE KPIS (Default) -->
                <!-- Can be replaced by JS when file is loaded -->
                <?php if (!empty($kpis)): ?>
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Volume Total</span>
                                <span
                                    class="text-2xl font-bold text-slate-800"><?= number_format(($kpis['total_amount'] ?? 0) / 1000000, 2) ?>
                                    M <span class="text-xs text-slate-400">DZD</span></span>
                            </div>
                            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
                                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Transactions</span>
                                <span class="text-2xl font-bold text-slate-800"><?= $kpis['total_trans'] ?? 0 ?></span>
                            </div>
                <?php else: ?>
                            <div
                                class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-center text-center text-slate-400 italic">
                                En attente de données...
                            </div>
                <?php endif; ?>
            </div>

            <!-- FILTERS -->
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 mb-6 sticky top-2 z-20">
                <div class="flex flex-wrap items-end gap-4">
                    <!-- Date Range -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-500 uppercase">Période</label>
                        <div class="flex gap-2">
                            <input type="date" id="filterDateStart" value="<?= $filters['start'] ?? '' ?>"
                                class="p-2 border rounded text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="date" id="filterDateEnd" value="<?= $filters['end'] ?? '' ?>"
                                class="p-2 border rounded text-sm bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none">
                        </div>
                    </div>

                    <!-- Montant -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-500 uppercase">Montant (DZD)</label>
                        <div class="flex gap-2">
                            <input type="number" id="filterMinAmount" placeholder="Min"
                                value="<?= $filters['min_amount'] ?? '' ?>"
                                class="p-2 border rounded text-sm w-24 bg-slate-50">
                            <input type="number" id="filterMaxAmount" placeholder="Max"
                                value="<?= $filters['max_amount'] ?? '' ?>"
                                class="p-2 border rounded text-sm w-24 bg-slate-50">
                        </div>
                    </div>

                    <!-- Parties -->
                    <div class="flex flex-col gap-1 flex-grow">
                        <label class="text-xs font-semibold text-slate-500 uppercase">Parties Prenantes</label>
                        <div class="flex gap-2">
                            <select id="filterSender" class="p-2 border rounded text-sm w-full bg-slate-50">
                                <option value="">Tous les Émetteurs</option>
                            </select>
                            <select id="filterBeneficiary" class="p-2 border rounded text-sm w-full bg-slate-50">
                                <option value="">Tous les Bénéficiaires</option>
                            </select>
                        </div>
                    </div>

                    <!-- Swift Type -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-semibold text-slate-500 uppercase">Type SWIFT</label>
                        <select id="filterSwift" class="p-2 border rounded text-sm w-32 bg-slate-50">
                            <option value="">Tous</option>
                            <option value="103" <?= ($filters['type'] ?? '') == 'MT103' ? 'selected' : '' ?>>MT103</option>
                            <option value="202" <?= ($filters['type'] ?? '') == 'MT202' ? 'selected' : '' ?>>MT202</option>
                        </select>
                    </div>


                    <!-- Save Session -->
                    <button id="saveSessionBtn"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium transition flex items-center gap-2">
                        <span>💾</span> Sauvegarder
                    </button>

                    <!-- Reset -->
                    <button id="resetFilters" onclick="window.location.href='index.php?action=dashboard'"
                        class="px-4 py-2 bg-slate-100 text-slate-600 rounded-lg hover:bg-slate-200 text-sm font-medium transition">
                        Réinitialiser
                    </button>
                </div>
            </div>

            <!-- CHARTS ROW 1 (Volume & Overview) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-sm text-slate-700 mb-4">Évolution du Volume journalier (30J)</h3>
                    <canvas id="volumeChart"></canvas>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-sm text-slate-700 mb-4">Types de Transactions</h3>
                    <canvas id="typeChart"></canvas>
                </div>
            </div>

            <!-- CHARTS ROW 2 (Top Parties) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-sm text-slate-700 mb-4">Top 5 Émetteurs (Montant)</h3>
                    <canvas id="topSenderChart"></canvas>
                </div>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200">
                    <h3 class="font-bold text-sm text-slate-700 mb-4">Top 5 Bénéficiaires (Montant)</h3>
                    <canvas id="topBenefChart"></canvas>
                </div>
            </div>

            <!-- MAIN CONTENT GRID -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <!-- TABLE (Full Width now or keep side charts? Let's keep table prominent and put simple lists on side or just make table full width if charts are above) -->
                <!-- User wants distinct charts. Let's make table full width below charts for better view -->
                <div
                    class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col h-[600px]">
                    <!-- ... TABLE HEADER ... -->
                    <div class="p-4 bg-slate-50 border-b flex justify-between items-center">
                        <h3 class="font-bold text-slate-700 flex items-center gap-2">
                            Transactions Récentes
                        </h3>
                        <span class="text-xs font-mono bg-blue-100 text-blue-700 px-2 py-1 rounded">
                            <span id="rowCount"><?= count($searchResults) ?></span> lignes
                        </span>
                    </div>

                    <div class="overflow-auto flex-grow relative table-container">
                        <table class="w-full text-left border-collapse relative">
                            <thead
                                class="bg-slate-100 text-[11px] uppercase text-slate-500 font-semibold sticky top-0 z-10 shadow-sm">
                                <tr>
                                    <th class="p-3">Date</th>
                                    <th class="p-3">Chrono</th>
                                    <th class="p-3">Type</th>
                                    <th class="p-3">Émetteur</th>
                                    <th class="p-3">Bénéficiaire</th>
                                    <th class="p-3">Montant</th>
                                    <th class="p-3 text-center">Statut Log</th>
                                </tr>
                            </thead>
                            <tbody id="dataTableBody" class="text-xs">
                                <?php if (empty($searchResults)): ?>
                                            <tr>
                                                <td colspan="7" class="p-8 text-center text-slate-400">Aucune donnée</td>
                                            </tr>
                                <?php else: ?>
                                            <?php foreach ($searchResults as $row): ?>
                                                        <tr class="border-b hover:bg-slate-50">
                                                            <td class="p-3"><?= $row['dco'] ?></td>
                                                            <td class="p-3 font-mono"><?= $row['chrono'] ?></td>
                                                            <td class="p-3"><span class="tag tag-blue"><?= $row['trans_type'] ?></span></td>
                                                            <td class="p-3 truncate max-w-[150px]" title="<?= $row['sender'] ?>">
                                                                <?= $row['sender'] ?>
                                                            </td>
                                                            <td class="p-3 truncate max-w-[150px]" title="<?= $row['beneficiary'] ?>">
                                                                <?= $row['beneficiary'] ?>
                                                            </td>
                                                            <td class="p-3 font-mono text-right"><?= number_format($row['amount'], 2) ?> <span
                                                                    class="text-[10px] text-slate-400"><?= $row['currency'] ?? 'DZD' ?></span>
                                                            </td>
                                                            <td class="p-3 text-center">
                                                                <?php if ($row['log_id']): ?>
                                                                            <span
                                                                                class="px-2 py-1 rounded bg-green-100 text-green-700 text-[10px] font-bold border border-green-200">
                                                                                <?= $row['swift_status'] ?: 'MATCHED' ?>
                                                                            </span>
                                                                <?php else: ?>
                                                                            <span
                                                                                class="inline-flex items-center gap-1 px-2 py-1 rounded bg-red-50 text-red-600 text-[10px] font-bold border border-red-100"
                                                                                title="Log manquant">
                                                                                NO LOG
                                                                            </span>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                            <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <script>
            // Server Data
            const volData = <?= json_encode($volParams ?? []) ?>;
            const typeData = <?= json_encode($typeStats ?? []) ?>;
            // New Data placeholders - Dashboard.php is now updated but we need to pass them from controller/view
            // We'll assume $topSenders and $topBenefs are passed. If not, chart will be empty which is fine.
            const topSendersData = <?= json_encode($topSenders ?? []) ?>;
            const topBenefsData = <?= json_encode($topBenefs ?? []) ?>;

            // --- STATE ---
            let rawData = [];
            let filteredData = [];
            let chartInstances = {};
            let isClientMode = false;

            // --- DOM ELEMENTS ---
            const els = {
                dropZone: document.getElementById('dropZone'),
                fileInput: document.getElementById('fileInput'),
                tableBody: document.getElementById('dataTableBody'),
                kpiContainer: document.getElementById('kpiContainer'),
                rowCount: document.getElementById('rowCount'),
                filters: {
                    start: document.getElementById('filterDateStart'),
                    end: document.getElementById('filterDateEnd'),
                    min: document.getElementById('filterMinAmount'),
                    max: document.getElementById('filterMaxAmount'),
                    sender: document.getElementById('filterSender'),
                    benef: document.getElementById('filterBeneficiary'),
                    swift: document.getElementById('filterSwift'),
                    reset: document.getElementById('resetFilters')
                }
            };

            // --- INITIALIZATION (Server Data) ---
            document.addEventListener('DOMContentLoaded', () => {
                // 1. Volume Chart (BAR)
                if (document.getElementById('volumeChart')) {
                    chartInstances.volume = new Chart(document.getElementById('volumeChart'), {
                        type: 'bar', // CHANGED to bar
                        data: {
                            labels: volData.map(d => d.date_val),
                            datasets: [{
                                label: 'Transactions',
                                data: volData.map(d => d.count),
                                backgroundColor: '#3b82f6',
                                borderRadius: 4
                            }]
                        },
                        options: {
                            scales: { y: { beginAtZero: true } }
                        }
                    });
                }

                // 2. Type Chart
                if (document.getElementById('typeChart')) {
                    chartInstances.type = new Chart(document.getElementById('typeChart'), {
                        type: 'doughnut',
                        data: {
                            labels: typeData.map(d => d.trans_type),
                            datasets: [{
                                data: typeData.map(d => d.count),
                                backgroundColor: ['#6366f1', '#ec4899', '#f59e0b', '#84cc16']
                            }]
                        }
                    });
                }

                // 3. Top Senders (New)
                if (document.getElementById('topSenderChart')) {
                    chartInstances.topSender = new Chart(document.getElementById('topSenderChart'), {
                        type: 'bar',
                        indexAxis: 'y', // Horizontal Bar
                        data: {
                            labels: topSendersData.map(d => d.sender || 'Inconnu'),
                            datasets: [{
                                label: 'Montant Total',
                                data: topSendersData.map(d => d.total_amount),
                                backgroundColor: '#10b981'
                            }]
                        }
                    });
                }

                // 4. Top Beneficiaries (New)
                if (document.getElementById('topBenefChart')) {
                    chartInstances.topBenef = new Chart(document.getElementById('topBenefChart'), {
                        type: 'bar',
                        indexAxis: 'y',
                        data: {
                            labels: topBenefsData.map(d => d.beneficiary || 'Inconnu'),
                            datasets: [{
                                label: 'Montant Total',
                                data: topBenefsData.map(d => d.total_amount),
                                backgroundColor: '#f59e0b'
                            }]
                        }
                    });
                }
            });

            // --- PIPELINE & FILE HANDLING ---
            window.addEventListener('DOMContentLoaded', () => {
                const urlParams = new URLSearchParams(window.location.search);
                const sessionId = urlParams.get('session_id');

                // Always load from IndexedDB (pipeline data)
                // If session_id is specified, we'll apply the saved filters after loading
                showLoading('Chargement des données', 'Récupération depuis IndexedDB...');
                console.log("Checking IndexedDB for persistent session data...");
                const request = indexedDB.open("RTGS_Analytics_DB", 2);

                request.onsuccess = async function (event) {
                    const db = event.target.result;
                    // Check if store exists
                    if (!db.objectStoreNames.contains('transfers')) {
                        console.log("IndexedDB store 'transfers' not found");
                        return;
                    }

                    const transaction = db.transaction(['transfers'], 'readonly');
                    const store = transaction.objectStore('transfers');
                    const getRequest = store.get('pipeline_output');

                    getRequest.onsuccess = async function (e) {
                        const result = e.target.result;
                        if (result && result.content) {
                            console.log("Restoring session data from IndexedDB...");
                            showLoading('Traitement des données', 'Parsing du SQL...');

                            // Process SQL client-side for display
                            processSQL(result.content);

                            // If session_id is specified, apply the saved filters after data loads
                            if (sessionId) {
                                console.log("Applying saved filters for session", sessionId);
                                showLoading('Application des filtres', 'Filtrage en cours...');
                                // The filters are already pre-filled by PHP, just trigger the filter application
                                setTimeout(() => {
                                    applyFilters();
                                    hideLoading();
                                }, 500); // Wait for data to be processed
                            } else {
                                hideLoading();
                            }
                        } else {
                            console.log("No pipeline data found in IndexedDB");
                        }
                    };

                    getRequest.onerror = function (e) {
                        console.error("Failed to retrieve data from IndexedDB", e);
                    };
                };

                request.onerror = function (e) {
                    console.error("Failed to open IndexedDB", e);
                };

                // Remove legacy localStorage logic to avoid conflicts
                localStorage.removeItem('rtgs_sql_data');


                // --- EVENT LISTENERS ---
                els.dropZone.onclick = () => els.fileInput.click();
                els.dropZone.ondragover = (e) => { e.preventDefault(); els.dropZone.classList.add('over'); };
                els.dropZone.ondragleave = () => els.dropZone.classList.remove('over');
                els.dropZone.ondrop = (e) => {
                    e.preventDefault();
                    els.dropZone.classList.remove('over');
                    handleFile(e.dataTransfer.files[0]);
                };
                els.fileInput.onchange = (e) => handleFile(e.target.files[0]);

                els.filters.reset.onclick = () => {
                    if (isClientMode) {
                        Object.values(els.filters).forEach(f => { if (f && f !== els.filters.reset) f.value = ''; });
                        applyFilters();
                    }
                };

                // Attach Input Listeners for Client Mode dynamic filtering
                Object.values(els.filters).forEach(el => {
                    if (el && el !== els.filters.reset) el.addEventListener('input', () => {
                        if (isClientMode) applyFilters();
                    });
                });

                // --- CORE CLIENT LOGIC ---
                function handleFile(file) {
                    isClientMode = true;

                    // Disconnect Server Filter buttons for clarity or just override behavior
                    els.filters.reset.onclick = () => {
                        document.querySelectorAll('input, select').forEach(i => i.value = '');
                        filteredData = rawData;
                        filters = {}; // reset state
                        updateUI();
                    }

                    const reader = new FileReader();
                    reader.onload = (e) => processSQL(e.target.result);
                    reader.readAsText(file);
                }

                function processSQL(text) {
                    isClientMode = true;
                    console.time("Parsing");

                    // Hide Dropzone & Show Indicator
                    document.getElementById('dropZone').classList.add('hidden');
                    document.getElementById('statusIndicator').classList.remove('hidden');

                    // 1. EXTRACT SWIFT LOGS (rtgs_message_log)
                    const logMap = new Map();
                    const logRegex = /INSERT INTO .*?rtgs_message_log.*?VALUES\s*(.*?;)/gis;
                    const rowValueRegex = /\((.*?)\)/gs;

                    let logMatch;
                    while ((logMatch = logRegex.exec(text)) !== null) {
                        let row;
                        while ((row = rowValueRegex.exec(logMatch[1])) !== null) {
                            const vals = splitCSV(row[1]);
                            if (vals.length > 7) {
                                logMap.set(vals[7], {
                                    date: vals[0],
                                    dir: vals[1],
                                    status: vals[2],
                                    ack: vals[3],
                                    type: vals[6]
                                });
                            }
                        }
                    }

                    // 2. EXTRACT TRANSACTIONS (sprtgs103 & sprtgs202)
                    const transRegex = /INSERT INTO .*?(sprtgs103|sprtgs202).*?VALUES\s*(.*?;)/gis;
                    const newData = [];
                    let transMatch;

                    while ((transMatch = transRegex.exec(text)) !== null) {
                        let row;
                        const fullMatch = transMatch[0];
                        const is202 = fullMatch.toLowerCase().includes('sprtgs202');
                        const block = transMatch[2];

                        // Define Indices
                        const IDX = is202
                            ? { mon: 18, ref: 6, dco: 3, chrono: 7, cur: 2, send: 15, ben: 14 }
                            : { mon: 33, ref: 6, dco: 3, chrono: 7, cur: 2, send: 19, ben: 28 };

                        const localRowRegex = /\((.*?)\)/gs;
                        while ((row = localRowRegex.exec(block)) !== null) {
                            const vals = splitCSV(row[1]);

                            if (vals.length > IDX.mon) {
                                const amountStr = vals[IDX.mon] || "0";
                                const amount = amountStr === 'NULL' ? 0 : parseFloat(amountStr.replace(',', '.'));

                                const ref = vals[IDX.ref] || "NONREF";
                                const dateStr = vals[IDX.dco] || "N/A";
                                const transDate = parseDate(dateStr);

                                let log = logMap.get(ref);

                                // Implicit SWIFT Fallback & Hints
                                let swiftInfo = {
                                    type: is202 ? "202" : "103",
                                    dir: vals[1] === 'S' ? 'OUT' : 'IN',
                                    status: 'NO_LOG',
                                    ack: 'NO LOG',
                                    isImplicit: true,
                                    match: false
                                };

                                if (log) {
                                    // Check Date consistency (optional strictness)
                                    swiftInfo = {
                                        type: log.type,
                                        dir: log.dir === 'S' ? 'OUT' : 'IN',
                                        status: log.status,
                                        ack: log.ack,
                                        isImplicit: false,
                                        match: true
                                    };
                                } else {
                                    // Fallback logic
                                    const lib = vals[8] || "";
                                    if (lib.includes("103")) swiftInfo.type = "103";
                                    else if (lib.includes("202")) swiftInfo.type = "202";
                                }

                                newData.push({
                                    id: vals[IDX.chrono],
                                    date: transDate,
                                    dateDisplay: dateStr,
                                    agency: vals[0],
                                    sender: vals[IDX.send] || "N/A",
                                    beneficiary: vals[IDX.ben] || "N/A",
                                    amount: amount,
                                    currency: vals[IDX.cur] === '208' ? 'DZD' : (vals[IDX.cur] || 'DZD'),
                                    trans_type: swiftInfo.type,
                                    swift: swiftInfo
                                });
                            }
                        }
                    }

                    console.timeEnd("Parsing");
                    rawData = newData;
                    filteredData = newData;
                    populateFilterOptions();
                    updateUI();
                }

                // --- UTILS ---
                function splitCSV(str) {
                    const res = [];
                    let curr = '';
                    let inQuote = false;
                    for (let i = 0; i < str.length; i++) {
                        const char = str[i];
                        if (char === "'") inQuote = !inQuote;
                        else if (char === ',' && !inQuote) {
                            res.push(curr.trim().replace(/^'|'$/g, ''));
                            curr = '';
                        } else {
                            curr += char;
                        }
                    }
                    res.push(curr.trim().replace(/^'|'$/g, ''));
                    return res;
                }

                function parseDate(str) {
                    if (!str) return null;
                    // Handle DD/MM/YYYY
                    if (str.includes('/')) {
                        const p = str.split('/');
                        return new Date(`${p[2]}-${p[1]}-${p[0]}`);
                    }
                    // Handle YYYY-MM-DD
                    if (str.includes('-')) {
                        return new Date(str);
                    }
                    return null;
                }

                function populateFilterOptions() {
                    const senders = new Set(rawData.map(d => d.sender).sort());
                    const benefs = new Set(rawData.map(d => d.beneficiary).sort());

                    const fill = (sel, set, savedValue) => {
                        sel.innerHTML = '<option value="">Tous</option>';
                        set.forEach(v => {
                            if (v && v !== "N/A") {
                                const selected = (v === savedValue) ? 'selected' : '';
                                sel.innerHTML += `<option value="${v}" ${selected}>${v}</option>`;
                            }
                        });
                    };

                    // Get saved values from PHP (if any)
                    const savedSender = '<?= $filters['sender'] ?? '' ?>';
                    const savedBenef = '<?= $filters['beneficiary'] ?? '' ?>';

                    fill(els.filters.sender, senders, savedSender);
                    fill(els.filters.benef, benefs, savedBenef);
                }

                function applyFilters() {
                    const dFrom = els.filters.start.value ? new Date(els.filters.start.value) : null;
                    const dTo = els.filters.end.value ? new Date(els.filters.end.value) : null;
                    const emit = els.filters.sender.value;
                    const recip = els.filters.benef.value;
                    const type = els.filters.swift.value;
                    const min = parseFloat(els.filters.min.value) || 0;
                    const max = parseFloat(els.filters.max.value) || Infinity;

                    filteredData = rawData.filter(d => {
                        if (dFrom && d.date && d.date < dFrom) return false;
                        if (dTo && d.date && d.date > dTo) return false;
                        if (emit && d.sender !== emit) return false;
                        if (recip && d.beneficiary !== recip) return false;
                        if (type && d.trans_type !== type && (!d.trans_type.includes(type))) return false;
                        if (d.amount < min || d.amount > max) return false;
                        return true;
                    });

                    updateUI();
                }

                function updateUI() {
                    // 1. KPIs
                    const totalVol = filteredData.reduce((acc, d) => acc + d.amount, 0);
                    const matched = filteredData.filter(d => d.swift.match).length;
                    const matchRate = rawData.length ? Math.round((matched / rawData.length) * 100) : 0;
                    const total = filteredData.length;

                    els.kpiContainer.innerHTML = `
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Volume Total</span>
                <span class="text-2xl font-bold text-slate-800">${(totalVol / 1000000).toFixed(2)} M <span class="text-xs text-slate-400">DZD</span></span>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Transactions</span>
                <span class="text-2xl font-bold text-slate-800">${total}</span>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Taux Matching</span>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl font-bold ${matchRate < 50 ? 'text-red-500' : 'text-green-500'}">${matchRate}%</span>
                    <span class="text-xs text-slate-400">(${matched}/${total} logs)</span>
                </div>
            </div>
            <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-100 flex flex-col justify-between">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Agences</span>
                <span class="text-2xl font-bold text-slate-800">${new Set(filteredData.map(d => d.agency)).size}</span>
            </div>
        `;

                    els.rowCount.innerText = total;

                    // 2. TABLE
                    const visibleData = filteredData.slice(0, 500);
                    els.tableBody.innerHTML = visibleData.map(d => {
                        // Badge Logic
                        let swiftClass, swiftLabel, statusBadge;
                        const itemType = d.trans_type.replace('MT', ''); // normalized

                        if (d.swift.isImplicit) {
                            swiftClass = 'bg-slate-100 text-slate-500';
                            swiftLabel = `MT${itemType} ${d.swift.dir}`;
                            statusBadge = `<span class="inline-flex items-center gap-1 px-2 py-1 rounded bg-red-50 text-red-600 text-[10px] font-bold border border-red-100" title="Log manquant pour le ${d.dateDisplay}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    NO LOG
                </span>`;
                        } else {
                            swiftClass = d.swift.dir === 'OUT' ? 'bg-indigo-50 text-indigo-600 border border-indigo-100' : 'bg-purple-50 text-purple-600 border border-purple-100';
                            swiftLabel = `MT${itemType} ${d.swift.dir}`;

                            let ackClass = 'bg-amber-50 text-amber-600 border-amber-100';
                            if (d.swift.ack === 'ACK') ackClass = 'bg-emerald-50 text-emerald-600 border-emerald-100';

                            statusBadge = `<span class="px-2 py-1 rounded ${ackClass} text-[10px] font-bold border">${d.swift.ack}</span>`;
                        }

                        return `
            <tr class="border-b border-slate-50 hover:bg-blue-50/30 transition group">
                <td class="p-3 font-mono text-slate-500 text-xs">${d.dateDisplay}</td>
                <td class="p-3 font-mono text-slate-500 text-xs">${d.id}</td>
                <td class="p-3"><span class="px-2 py-1 rounded text-[10px] font-bold ${swiftClass}">${swiftLabel}</span></td>
                <td class="p-3 text-slate-700 truncate max-w-[200px]" title="${d.sender}">${d.sender}</td>
                <td class="p-3 text-slate-700 truncate max-w-[200px]" title="${d.beneficiary}">${d.beneficiary}</td>
                <td class="p-3 text-right font-mono font-medium text-slate-700">${d.amount.toLocaleString('fr-FR', { minimumFractionDigits: 2 })} <span class="text-[10px] text-slate-400">${d.currency}</span></td>
                <td class="p-3 text-center">${statusBadge}</td>
            </tr>`;
                    }).join('');

                    // 3. Update Charts
                    updateCharts(filteredData);
                }

                function updateCharts(data) {
                    // Destroy old instances
                    ['volume', 'type', 'topSender', 'topBenef'].forEach(k => {
                        if (chartInstances[k]) {
                            chartInstances[k].destroy();
                            chartInstances[k] = null;
                        }
                    });

                    // 1. Volume (Bar)
                    const volMap = new Map();
                    data.forEach(d => {
                        if (!d.date) return;
                        const key = d.date.toISOString().split('T')[0];
                        volMap.set(key, (volMap.get(key) || 0) + 1);
                    });
                    const sortedDates = Array.from(volMap.keys()).sort();

                    chartInstances.volume = new Chart(document.getElementById('volumeChart'), {
                        type: 'bar',
                        data: {
                            labels: sortedDates,
                            datasets: [{
                                label: 'Transactions',
                                data: sortedDates.map(k => volMap.get(k)),
                                backgroundColor: '#3b82f6',
                                borderRadius: 4
                            }]
                        },
                        options: { scales: { y: { beginAtZero: true } } }
                    });

                    // 2. Type
                    const typeCounts = {};
                    data.forEach(d => typeCounts[d.trans_type] = (typeCounts[d.trans_type] || 0) + 1);
                    chartInstances.type = new Chart(document.getElementById('typeChart'), {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(typeCounts),
                            datasets: [{
                                data: Object.values(typeCounts),
                                backgroundColor: ['#6366f1', '#ec4899', '#f59e0b', '#84cc16']
                            }]
                        }
                    });

                    // 3. Top Senders
                    const sendMap = {};
                    data.forEach(d => sendMap[d.sender] = (sendMap[d.sender] || 0) + d.amount);
                    const topSend = Object.entries(sendMap).sort((a, b) => b[1] - a[1]).slice(0, 5);

                    chartInstances.topSender = new Chart(document.getElementById('topSenderChart'), {
                        type: 'bar',
                        indexAxis: 'y',
                        data: {
                            labels: topSend.map(x => x[0]),
                            datasets: [{
                                label: 'Montant',
                                data: topSend.map(x => x[1]),
                                backgroundColor: '#10b981'
                            }]
                        }
                    });

                    // 4. Top Beneficiaries
                    const benMap = {};
                    data.forEach(d => benMap[d.beneficiary] = (benMap[d.beneficiary] || 0) + d.amount);
                    const topBen = Object.entries(benMap).sort((a, b) => b[1] - a[1]).slice(0, 5);

                    chartInstances.topBenef = new Chart(document.getElementById('topBenefChart'), {
                        type: 'bar',
                        indexAxis: 'y',
                        data: {
                            labels: topBen.map(x => x[0]),
                            datasets: [{
                                label: 'Montant',
                                data: topBen.map(x => x[1]),
                                backgroundColor: '#f59e0b'
                            }]
                        }
                    });
                }
                // --- SAVE SESSION LOGIC ---
                document.getElementById('saveSessionBtn').addEventListener('click', async () => {
                    const name = prompt("Nom de la nouvelle session sauvegardée :");
                    if (!name) return;

                    showLoading('Sauvegarde en cours', 'Enregistrement du rapport...');

                    const filters = {
                        session_id: '<?= $filters['session_id'] ?? '' ?>',
                        start: els.filters.start?.value || '',
                        end: els.filters.end?.value || '',
                        min_amount: els.filters.min?.value || '',
                        max_amount: els.filters.max?.value || '',
                        type: els.filters.swift?.value || '',
                        sender: els.filters.sender?.value || '',
                        beneficiary: els.filters.benef?.value || '',
                    };

                    try {
                        const response = await fetch('index.php?action=save_session', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ ...filters, name })
                        });
                        const result = await response.json();
                        
                        hideLoading();
                        
                        if (result.status === 'success') {
                            if (confirm('Session sauvegardée avec succès ! Voulez-vous la voir maintenant ?')) {
                                window.location.href = 'index.php?action=dashboard&session_id=' + result.new_session_id;
                            }
                        } else {
                            alert('Erreur: ' + result.message);
                        }
                    } catch (e) {
                        hideLoading();
                        alert('Erreur réseau: ' + e.message);
                    }
                });

            }); // Close DOMContentLoaded event listener
        </script>
        <?php require_once __DIR__ . '/footer.php'; ?>