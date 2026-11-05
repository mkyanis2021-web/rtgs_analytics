<?php
// templates/etl.php
// PROTECTED BY RBAC
if (session_status() === PHP_SESSION_NONE)
    session_start();
if (!isset($_SESSION['user_id'])) {
    die("Accès refusé. Veuillez vous connecter.");
}
// Allow specific roles (e.g., Admin)
if ($_SESSION['role'] !== 'Admin') {
    die("Accès réservé aux Administrateurs.");
}
require_once __DIR__ . '/header.php';
?>
<!-- LOCAL STYLES FOR PIPELINE WIZARD (Overrides minimal defaults where needed) -->
<style>
    /* Pipeline Specifics */
    .pipeline-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background: white;
        padding: 20px 0;
        box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .step-indicator {
        display: flex;
        align-items: center;
        justify-content: center;
        max-width: 800px;
        margin: 0 auto;
    }

    .step {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 140px;
        position: relative;
        z-index: 2;
        cursor: pointer;
    }

    .step-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: #e2e8f0;
        color: var(--text-light);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2em;
        transition: all 0.3s ease;
        border: 4px solid white;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    .step.active .step-circle {
        background: var(--primary);
        color: white;
        transform: scale(1.1);
        box-shadow: 0 0 15px rgba(102, 126, 234, 0.6);
    }

    .step.completed .step-circle {
        background: var(--success);
        color: white;
    }

    .step-label {
        margin-top: 8px;
        font-size: 0.85em;
        color: #718096;
        font-weight: 600;
    }

    .step.active .step-label {
        color: var(--primary);
    }

    .connector {
        flex-grow: 1;
        height: 4px;
        background: #e2e8f0;
        margin: 0 -40px;
        position: relative;
        top: -15px;
        z-index: 1;
        transition: background 0.5s ease;
    }

    .connector.filled {
        background: var(--success);
    }

    .file-input-wrapper {
        margin: 20px 0;
        text-align: center;
        border: 2px dashed #cbd5e0;
        padding: 30px;
        border-radius: 10px;
        background: #f8fafc;
    }

    .file-input-label {
        display: inline-block;
        padding: 12px 30px;
        background: var(--primary);
        color: white;
        border-radius: 6px;
        cursor: pointer;
        font-weight: bold;
        transition: transform 0.2s;
    }

    input[type=file] {
        display: none;
    }

    .action-btn {
        padding: 15px 40px;
        border: none;
        border-radius: 8px;
        font-size: 1.1em;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.2s;
        color: white;
        margin: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .btn-analyze {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        /* Uses vars from header if defined, else fallbacks */
        background: linear-gradient(135deg, #2563eb 0%, #475569 100%);
    }

    .btn-next {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    }

    .btn-fusion {
        background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
    }

    .btn-download {
        background: #2d3748;
    }

    .step-section {
        display: none;
    }

    .step-section.active {
        display: block;
        animation: fadeIn 0.4s ease-out;
    }

    textarea {
        width: 100%;
        height: 150px;
        padding: 15px;
        border: 1px solid #cbd5e0;
        border-radius: 8px;
        font-family: monospace;
        resize: vertical;
    }

    .console-output {
        background: #1a202c;
        color: #48bb78;
        font-family: 'Courier New', monospace;
        padding: 20px;
        border-radius: 8px;
        height: 250px;
        overflow-y: auto;
        white-space: pre-wrap;
        margin-top: 20px;
        font-size: 0.9em;
    }

    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .type-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 10px;
    }

    .type-badge {
        background: #edf2f7;
        border-radius: 8px;
        padding: 10px;
        text-align: center;
        border: 1px solid #cbd5e0;
    }

    .progress-container {
        width: 100%;
        background-color: #e2e8f0;
        border-radius: 10px;
        height: 24px;
        margin: 10px 0 20px 0;
        overflow: hidden;
        display: flex;
    }

    .progress-bar {
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75em;
        color: white;
        font-weight: bold;
    }

    .bg-sent {
        background-color: #475569;
    }

    .bg-recv {
        background-color: #3b82f6;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div id="step1-section" class="step-section active">
    <div class="card">
        <h1>1. Analyseur de Logs RTGS</h1>
        <p class="subtitle" style="color:#64748b; margin-bottom:20px;">Analyse détaillée du fichier log brut (Système
            A).</p>

        <div class="file-input-wrapper">
            <input type="file" id="logFileInput" accept=".txt,.log">
            <label for="logFileInput" class="file-input-label">📂 Charger Fichier Log</label>
            <p style="margin-top:10px; font-size:0.9em; color:#718096;">Ou coller le contenu ci-dessous</p>
        </div>

        <textarea id="logContent" placeholder="Ex: 2025/07/16 15:20:39|R| OK|..."></textarea>

        <div style="text-align: center; margin-top: 20px;">
            <button class="action-btn btn-analyze" onclick="processLogStep()">Analyser le Log</button>
        </div>
    </div>

    <div id="logResults" class="card" style="display: none;">
        <h2>📊 Insights du Log</h2>

        <div class="dashboard-grid">
            <div class="kpi-card">
                <div class="kpi-val" id="kpiTotal">0</div>
                <div class="kpi-label">Lignes traitées</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-val" id="kpiSuccess" style="color:var(--success)">100%</div>
                <div class="kpi-label" id="kpiErrorCount">0 Erreurs</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-val" id="kpiSent" style="color:#475569">0</div>
                <div class="kpi-label">Messages Émis</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-val" id="kpiRecv" style="color:#3b82f6">0</div>
                <div class="kpi-label">Messages Reçus</div>
            </div>
        </div>

        <h3>Balance des Flux</h3>
        <div class="progress-container" id="fluxBar"></div>

        <h3>Répartition par Type SWIFT</h3>
        <div class="type-grid" id="typeDistribution"></div>

        <div style="text-align: center; margin-top: 40px;">
            <button class="action-btn btn-next" onclick="goToStep(2)">Suivant : Transactions (UNL) ➡️</button>
        </div>
    </div>
</div>

<div id="step2-section" class="step-section">
    <div class="card">
        <h1>2. Import Transactions (UNL)</h1>
        <p class="subtitle" style="color:#64748b; margin-bottom:20px;">Conversion des exports Informix (Système B) vers
            SQL Server.</p>

        <div class="file-input-wrapper">
            <input type="file" id="unlFileInput" accept=".unl" multiple>
            <label for="unlFileInput" class="file-input-label">📂 Charger Fichiers UNL</label>
        </div>

        <div style="text-align: center;">
            <button class="action-btn btn-analyze" onclick="processUnlStep()">Traiter les Fichiers</button>
        </div>

        <div id="unlConsole" class="console-output" style="display:none;"></div>
    </div>

    <div id="unlResults" class="card" style="display: none;">
        <div style="text-align: center;">
            <h3 style="color: var(--success); margin-bottom: 20px;">Traitement terminé avec succès</h3>
            <button class="action-btn btn-fusion" onclick="goToStep(3)">Suivant : Fusion & Intégration ➡️</button>
        </div>
    </div>
</div>

<div id="step3-section" class="step-section">
    <div class="card">
        <h1>3. Fusion et Intégration</h1>
        <p class="subtitle" style="color:#64748b; margin-bottom:20px;">Génération du script SQL unifié incluant la
            structure, les données et la logique de matching.</p>

        <div
            style="background: #ebf8ff; padding: 20px; border-radius: 8px; border-left: 5px solid #4299e1; margin-bottom: 20px;">
            <h3>Résumé de l'Intégration</h3>
            <ul style="margin-left: 20px; margin-top: 10px; color: #2c5282;">
                <li><strong>Base de données :</strong> RTGS_INTEGRATED_DB</li>
                <li><strong>Patch Appliqué :</strong> Colonnes 'eve' étendues à VARCHAR(50)</li>
                <li><strong>Données Log :</strong> <span id="summaryLogCount">0</span> lignes (Prêt à insérer)</li>
                <li><strong>Données UNL :</strong> <span id="summaryUnlCount">0</span> tables traitées (Prêt à insérer)
                </li>
                <li><strong>Logique :</strong> Procédures stockées et Vues de réconciliation incluses</li>
            </ul>
        </div>

        <div style="text-align: center;">
            <button class="action-btn btn-fusion" onclick="generateFusionScript()">⚡ Générer Script "Gold
                Standard"</button>
        </div>
    </div>

    <div id="finalResult" class="card" style="display: none; text-align: center;">
        <h2 style="color: var(--success);">Script SQL Généré !</h2>
        <p>Ce script contient tout : Création DB, Tables, Insertion des données et Logique de Matching.</p>
        <br>
        <button class="action-btn btn-download" onclick="downloadFusion()">⬇️ Télécharger RTGS_FULL_SETUP.sql</button>
        <div style="margin-top:20px; padding:20px; background:#f0f9ff; border-radius:10px; border:1px solid #bae6fd;">
            <h3>🚀 Étape Suivante : Analyse & Réconciliation</h3>
            <p style="margin-bottom:15px; color:#0369a1;">Le script est prêt. Cliquez ci-dessous pour lancer le
                Dashboard d'analyse 360° et visualiser les données immédiatement.</p>
            <button class="action-btn btn-analyze" style="font-size:1.3em; padding:20px 50px;"
                onclick="openDashboard()">🚀 OUVRIR LE DASHBOARD</button>
        </div>
    </div>
</div>

<div class="pipeline-bar">
    <div class="step-indicator">
        <div class="step active" id="viz-step-1" onclick="if(this.classList.contains('completed')) goToStep(1)">
            <div class="step-circle">1</div>
            <div class="step-label">Logs (System A)</div>
        </div>
        <div class="connector" id="viz-conn-1"></div>

        <div class="step" id="viz-step-2" onclick="if(this.classList.contains('completed')) goToStep(2)">
            <div class="step-circle">2</div>
            <div class="step-label">UNL (System B)</div>
        </div>
        <div class="connector" id="viz-conn-2"></div>

        <div class="step" id="viz-step-3">
            <div class="step-circle">3</div>
            <div class="step-label">Fusion DB</div>
        </div>
    </div>
</div>

<script>
    // --- DATA STORAGE ---
    let logSqlData = "";
    let unlSqlStatements = "";
    let logCount = 0;
    let unlTableCount = 0;

    // --- SCHEMAS ---
    const tableSchemas = {
        'cpaentrtgss': ['chrono', 'modu', 'age', 'ope', 'eve', 'tdos', 'ndos', 'tmes', 'desti', 'eta'],
        'cpaentrtgse': ['chrono', 'modu', 'age', 'ope', 'eve', 'tdos', 'ndos', 'tmes', 'desti', 'eta'],
        'cpadetrtgss': ['chrono', 'nlig', 'mes'],
        'cpadetrtgse': ['chrono', 'nlig', 'mes'],
        'cpaactrtgss': ['chrono', 'dh', 'act', 'libe', 'uti'],
        'cpaactrtgse': ['chrono', 'dh', 'act', 'libe', 'uti'],
        'sprtgs103': ['age', 'i_o', 'dev', 'dco', 'ope', 'eve', 'ref', 'chrono', 'lib', 'mot', 'ncpd', 'ncpc', 'cptrcpa', 'cptrconf', 'ribe', 'etbe', 'agee', 'ncpe', 'clce', 'nome1', 'nome2', 'nome3', 'nome4', 'ribb', 'etbb', 'ageb', 'ncpb', 'clcb', 'nomb1', 'nomb2', 'nomb3', 'nomb4', 'sen', 'mon', 'eta', 'uti', 'typtr', 'mot1', 'ack', 'ref_ack1', 'ref_ack2', 'dmo', 'utm', 'dva', 'utv', 'uttr', 'dttr'],
        'sprtgs202': ['ope', 'eve', 'ref', 'chrono', 'lib', 'ncpd', 'ncpc', 'cptrcpa', 'cptrconf', 'eto', 'etab', 'bic', 'guib', 'sen', 'mon', 'eta', 'uti', 'typtr', 'mot1', 'mot2', 'mot3', 'mot4', 'mot5', 'chrono_clo', 'chrono_ret', 'ack', 'ref_ack1', 'ref_ack2', 'dmo', 'utm', 'dva', 'utv', 'uttr', 'dttr']
    };

    const numericColumns = ['chrono', 'nlig', 'mon', 'chrono_clo', 'chrono_ret'];
    const dateColumns = ['dco', 'dmo', 'dva', 'dttr'];
    const dateTimeColumns = ['dh'];

    // --- STEP 1: LOG PARSER (AMÉLIORÉ) ---
    document.getElementById('logFileInput').addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => document.getElementById('logContent').value = e.target.result;
            reader.readAsText(file);
        }
    });

    function processLogStep() {
        const content = document.getElementById('logContent').value.trim();
        if (!content) return alert("Veuillez charger un fichier log.");

        const lines = content.split('\n').filter(line => line.trim() && !line.includes('===') && !line.includes('Tracking'));

        // Analyse Statistiques
        let countTotal = 0;
        let countOk = 0;
        let countSent = 0;
        let countRecv = 0;
        let types = {};

        const values = lines.map(line => {
            const parts = line.split('|');
            if (parts.length < 5) return null;

            const dt = parts[0]?.trim().replace(/\//g, '-') || null;
            const dir = parts[1]?.trim();
            const status = parts[2]?.trim();
            const ack = parts[3]?.trim();
            const msgId = parts[4]?.trim();
            const bic = parts[5]?.trim();
            const type = parts[6]?.trim() || 'UNK';
            const f20 = parts[7]?.trim();
            const swf = parts[8]?.trim();
            const inp = parts[9]?.trim();
            const out = parts[10]?.trim();

            if (!dt) return null;

            // Aggregation Stats
            countTotal++;
            if (status === 'OK') countOk++;
            if (dir === 'S') countSent++;
            if (dir === 'R') countRecv++;

            // Count types (clean up '103', 'MT103', etc)
            let cleanType = type.replace('MT', '');
            types[cleanType] = (types[cleanType] || 0) + 1;

            return `('${dt}', '${dir}', '${status}', '${ack}', '${msgId}', '${bic}', '${type}', '${f20}', '${swf}', '${inp}', '${out}')`;
        }).filter(v => v !== null);

        logCount = countTotal;
        logSqlData = values.join(',\n    ');

        // --- MISE A JOUR DASHBOARD UI ---

        // 1. KPIs
        document.getElementById('kpiTotal').innerText = countTotal;
        const successRate = countTotal > 0 ? ((countOk / countTotal) * 100).toFixed(1) : 0;
        document.getElementById('kpiSuccess').innerText = successRate + '%';
        document.getElementById('kpiErrorCount').innerText = (countTotal - countOk) + ' Erreurs';
        if (countTotal - countOk > 0) document.getElementById('kpiErrorCount').style.color = 'var(--danger)';

        document.getElementById('kpiSent').innerText = countSent;
        document.getElementById('kpiRecv').innerText = countRecv;

        // 2. Flux Bar
        const pctSent = countTotal > 0 ? (countSent / countTotal) * 100 : 0;
        const pctRecv = countTotal > 0 ? (countRecv / countTotal) * 100 : 0;

        document.getElementById('fluxBar').innerHTML = `
            <div class="progress-bar bg-sent" style="width: ${pctSent}%">Envoyés (${Math.round(pctSent)}%)</div>
            <div class="progress-bar bg-recv" style="width: ${pctRecv}%">Reçus (${Math.round(pctRecv)}%)</div>
        `;

        // 3. Types Badges
        let badgesHtml = '';
        for (const [t, c] of Object.entries(types)) {
            badgesHtml += `<div class="type-badge"><strong>${c}</strong><span>MT${t}</span></div>`;
        }
        document.getElementById('typeDistribution').innerHTML = badgesHtml;

        // Update Summary Footer
        document.getElementById('summaryLogCount').innerText = logCount;
        document.getElementById('logResults').style.display = 'block';

        // Scroll smoothly
        document.getElementById('logResults').scrollIntoView({ behavior: 'smooth' });
    }

    // --- STEP 2: UNL PARSER ---
    function getTableName(filename) {
        let name = filename.split('.')[0];
        // Don't remove digits, just hyphens/underscores if needed, or better yet, just lowercase
        // Assuming filenames like sprtgs103.unl or sprtgs103-2025.unl
        // We want 'sprtgs103'
        // Simple heuristic directly for known tables:
        name = name.toLowerCase();
        if (name.includes('sprtgs103')) return 'sprtgs103';
        if (name.includes('sprtgs202')) return 'sprtgs202';
        if (name.includes('cpaentrtgss')) return 'cpaentrtgss';
        if (name.includes('cpaentrtgse')) return 'cpaentrtgse';
        if (name.includes('cpaactrtgss')) return 'cpaactrtgss';
        if (name.includes('cpaactrtgse')) return 'cpaactrtgse';
        if (name.includes('cpadetrtgss')) return 'cpadetrtgss';
        if (name.includes('cpadetrtgse')) return 'cpadetrtgse';
        // Fallback: try removing extra chars if it helps, but careful with digits
        return name.replace(/[^a-z0-9]/g, '');
    }

    function formatSqlValue(val, colName) {
        let v = val.trim();
        if (!v) return "NULL";

        if (numericColumns.includes(colName)) {
            // Replace comma with dot for SQL numeric format
            v = v.replace(',', '.');
            // Remove any potential trailing/leading crap if necessary, but just validation:
            return /^-?\d+(\.\d+)?$/.test(v) ? v : "NULL";
        }
        if (dateTimeColumns.includes(colName)) {
            const d = new Date(v);
            if (!isNaN(d)) return `'${d.toISOString().slice(0, 19).replace('T', ' ')}'`;
            return "NULL";
        }
        if (dateColumns.includes(colName)) {
            // Handle DD/MM/YYYY
            let match = v.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
            if (match) return `'${match[3]}-${match[2]}-${match[1]}'`;
            // Handle YYYY-MM-DD
            if (/^\d{4}-\d{2}-\d{2}$/.test(v)) return `'${v}'`;
            return "NULL";
        }
        // Escape single quotes
        return `'${v.replace(/'/g, "''")}'`;
    }

    async function processUnlStep() {
        const files = document.getElementById('unlFileInput').files;
        if (files.length === 0) return alert("Aucun fichier sélectionné.");

        const consoleDiv = document.getElementById('unlConsole');
        consoleDiv.style.display = 'block';
        consoleDiv.innerText = "Début du traitement...\n";

        unlSqlStatements = "";
        unlTableCount = 0;

        const fileArray = Array.from(files).sort((a, b) => a.name.localeCompare(b.name));

        for (let file of fileArray) {
            const tableName = getTableName(file.name);
            if (!tableSchemas[tableName]) {
                consoleDiv.innerText += `SKIP: ${file.name} (Schema inconnu)\n`;
                continue;
            }

            const text = await file.text();
            const lines = text.split('\n');
            const cols = tableSchemas[tableName];
            let batch = [];
            let count = 0;

            consoleDiv.innerText += `OK: ${file.name} -> ${tableName} `;
            unlSqlStatements += `-- Data for ${tableName}\n`;

            for (let line of lines) {
                if (!line.trim()) continue;
                const vals = line.split('|');
                const sqlVals = cols.map((col, idx) => formatSqlValue(vals[idx] || '', col));
                batch.push(`(${sqlVals.join(', ')})`);
                count++;

                if (batch.length >= 500) {
                    unlSqlStatements += `INSERT INTO bank.${tableName} (${cols.join(', ')}) VALUES\n${batch.join(',\n')};\nGO\n`;
                    batch = [];
                }
            }
            if (batch.length > 0) {
                unlSqlStatements += `INSERT INTO bank.${tableName} (${cols.join(', ')}) VALUES\n${batch.join(',\n')};\nGO\n`;
            }

            consoleDiv.innerText += `(${count} rows)\n`;
            unlTableCount++;
        }

        document.getElementById('summaryUnlCount').innerText = unlTableCount;
        document.getElementById('unlResults').style.display = 'block';
        window.scrollTo(0, document.body.scrollHeight);
    }

    // --- STEP 3: FUSION SCRIPT GENERATION ---
    const SQL_SKELETON_TOP = `
-- =====================================================
-- RTGS INTEGRATED DATABASE - AUTO GENERATED
-- =====================================================
USE master;
GO

IF EXISTS (SELECT name FROM sys.databases WHERE name = 'RTGS_INTEGRATED_DB')
BEGIN
    ALTER DATABASE RTGS_INTEGRATED_DB SET SINGLE_USER WITH ROLLBACK IMMEDIATE;
    DROP DATABASE RTGS_INTEGRATED_DB;
END
GO

CREATE DATABASE RTGS_INTEGRATED_DB;
GO

USE RTGS_INTEGRATED_DB;
GO

EXEC('CREATE SCHEMA bank');
GO

-- =====================================================
-- TABLE CREATION (MATCHING REFERENCE FILE)
-- =====================================================
            CREATE TABLE bank.rtgs_message_log (id INT IDENTITY PRIMARY KEY, log_datetime DATETIME2, direction CHAR(1), status VARCHAR(10), ack_type VARCHAR(10), message_id VARCHAR(50), bic_cpa VARCHAR(12), swift_type VARCHAR(3), field_20 VARCHAR(50), swift_file VARCHAR(100), input_file VARCHAR(100), output_file VARCHAR(100), created_date DATETIME2 DEFAULT GETDATE());
            
            CREATE TABLE bank.cpaentrtgss (chrono DECIMAL(8,0) PRIMARY KEY, modu CHAR(3), age CHAR(5), ope CHAR(3), eve VARCHAR(50), tdos CHAR(3), ndos VARCHAR(50), tmes CHAR(5), desti CHAR(12), eta CHAR(2), created_date DATETIME DEFAULT GETDATE());
            CREATE TABLE bank.cpaentrtgse (chrono DECIMAL(8,0) PRIMARY KEY, modu CHAR(3), age CHAR(5), ope CHAR(3), eve VARCHAR(50), tdos CHAR(3), ndos VARCHAR(50), tmes CHAR(5), desti CHAR(12), eta CHAR(2), created_date DATETIME DEFAULT GETDATE());
            
            CREATE TABLE bank.cpadetrtgss (chrono DECIMAL(8,0), nlig DECIMAL(4,0), mes VARCHAR(100));
            CREATE TABLE bank.cpadetrtgse (chrono DECIMAL(8,0), nlig DECIMAL(4,0), mes VARCHAR(100));
            
            CREATE TABLE bank.cpaactrtgss (chrono DECIMAL(8,0), dh DATETIME, act CHAR(5), libe VARCHAR(50), uti CHAR(4));
            CREATE TABLE bank.cpaactrtgse (chrono DECIMAL(8,0), dh DATETIME, act CHAR(5), libe VARCHAR(50), uti CHAR(4));

            CREATE TABLE bank.sprtgs103 (id BIGINT IDENTITY PRIMARY KEY, age CHAR(5), i_o CHAR(1), dev CHAR(3), dco DATE, ope CHAR(3), eve VARCHAR(50), ref VARCHAR(16), chrono DECIMAL(8,0), lib VARCHAR(40), mot VARCHAR(40), ncpd CHAR(10), ncpc CHAR(10), cptrcpa CHAR(10), cptrconf CHAR(10), ribe VARCHAR(30), etbe CHAR(5), agee CHAR(5), ncpe CHAR(10), clce CHAR(2), nome1 VARCHAR(30), nome2 VARCHAR(30), nome3 VARCHAR(30), nome4 VARCHAR(30), ribb VARCHAR(30), etbb CHAR(5), ageb CHAR(5), ncpb CHAR(10), clcb CHAR(2), nomb1 VARCHAR(30), nomb2 VARCHAR(30), nomb3 VARCHAR(30), nomb4 VARCHAR(30), sen CHAR(1), mon DECIMAL(19,2), eta CHAR(2), uti CHAR(4), typtr CHAR(3), mot1 VARCHAR(40), ack CHAR(1), ref_ack1 VARCHAR(16), ref_ack2 VARCHAR(16), dmo DATE, utm CHAR(4), dva DATE, utv CHAR(4), uttr CHAR(4), dttr DATE, created_date DATETIME DEFAULT GETDATE());
            CREATE TABLE bank.sprtgs202 (id BIGINT IDENTITY PRIMARY KEY, age CHAR(5), i_o CHAR(1), dev CHAR(3), dco DATE, ope CHAR(3), eve VARCHAR(50), ref VARCHAR(16), chrono DECIMAL(8,0), lib VARCHAR(40), ncpd CHAR(10), ncpc CHAR(10), cptrcpa CHAR(10), cptrconf CHAR(10), eto CHAR(5), etab CHAR(5), bic CHAR(12), guib CHAR(5), sen CHAR(1), mon DECIMAL(19,2), eta CHAR(2), uti CHAR(4), typtr CHAR(3), mot1 VARCHAR(35), mot2 VARCHAR(35), mot3 VARCHAR(35), mot4 VARCHAR(35), mot5 VARCHAR(35), chrono_clo DECIMAL(8,0), chrono_ret DECIMAL(8,0), ack CHAR(1), ref_ack1 VARCHAR(16), ref_ack2 VARCHAR(16), dmo DATE, utm CHAR(4), dva DATE, utv CHAR(4), uttr CHAR(4), dttr DATE, created_date DATETIME DEFAULT GETDATE());
            
            CREATE TABLE bank.rtgs_log_transaction_link (link_id BIGINT IDENTITY PRIMARY KEY, log_id INT, transaction_chrono DECIMAL(8,0), transaction_table VARCHAR(20), match_level VARCHAR(20), confidence_score DECIMAL(5,2), match_method VARCHAR(50), matched_on VARCHAR(100), time_diff_seconds INT, match_date DATETIME2 DEFAULT GETDATE());
            CREATE TABLE bank.rtgs_orphan_logs (orphan_id BIGINT IDENTITY(1,1) PRIMARY KEY, log_id INT NOT NULL, reason VARCHAR(200) NOT NULL, severity VARCHAR(20) NOT NULL, investigated BIT DEFAULT 0, created_date DATETIME2 DEFAULT GETDATE());
            CREATE TABLE bank.rtgs_orphan_transactions (orphan_id BIGINT IDENTITY(1,1) PRIMARY KEY, transaction_chrono DECIMAL(8,0) NOT NULL, transaction_table VARCHAR(20) NOT NULL, reason VARCHAR(200) NOT NULL, severity VARCHAR(20) NOT NULL, investigated BIT DEFAULT 0, created_date DATETIME2 DEFAULT GETDATE());
            CREATE TABLE bank.rtgs_reconciliation_summary (summary_id BIGINT IDENTITY(1,1) PRIMARY KEY, reconciliation_date DATE NOT NULL, total_logs INT NOT NULL, total_transactions INT NOT NULL, matched_logs INT NOT NULL, matched_transactions INT NOT NULL, orphan_logs INT NOT NULL, orphan_transactions INT NOT NULL, strong_matches INT NOT NULL, probable_matches INT NOT NULL, weak_matches INT NOT NULL, avg_confidence_score DECIMAL(5,2) NULL, processing_time_seconds INT NULL, processed_by VARCHAR(50) NULL, created_date DATETIME2 DEFAULT GETDATE(), CONSTRAINT UQ_reconciliation_date UNIQUE (reconciliation_date));
            GO

-- =====================================================
-- DATA INJECTION START
-- =====================================================
`;

    const SQL_MATCHING_PROCEDURE = `
-- =====================================================
-- MATCHING LOGIC & PROCEDURES
-- =====================================================
CREATE PROCEDURE bank.sp_match_logs_transactions
    @match_date DATE = NULL,
    @force_rematch BIT = 0
AS
BEGIN
    SET NOCOUNT ON;
    DECLARE @start_time DATETIME2 = GETDATE();
    
    IF @match_date IS NULL SET @match_date = CAST(GETDATE() AS DATE);
    
    IF @force_rematch = 1
    BEGIN
        DELETE FROM bank.rtgs_log_transaction_link WHERE CAST(match_date AS DATE) = @match_date;
        DELETE FROM bank.rtgs_reconciliation_summary WHERE reconciliation_date = @match_date;
        DELETE FROM bank.rtgs_orphan_logs WHERE CAST(created_date AS DATE) = @match_date;
        DELETE FROM bank.rtgs_orphan_transactions WHERE CAST(created_date AS DATE) = @match_date;
    END

    -- 1. STRONG MATCH (Ref + Type + Dir)
    INSERT INTO bank.rtgs_log_transaction_link (log_id, transaction_chrono, transaction_table, match_level, confidence_score, match_method, matched_on, time_diff_seconds)
    SELECT l.id, e.chrono, 'cpaentrtgss', 'STRONG', 98.0, 'Direct match on ref', l.field_20, DATEDIFF(SECOND, l.log_datetime, a.dh)
    FROM bank.rtgs_message_log l
    INNER JOIN bank.cpaentrtgss e ON l.swift_type = REPLACE(e.tmes, 'MT', '')
    INNER JOIN bank.sprtgs103 t ON e.chrono = t.chrono
    INNER JOIN bank.cpaactrtgss a ON e.chrono = a.chrono AND a.act = 'TRANS'
    WHERE l.field_20 = t.ref AND l.direction = 'S' AND t.i_o = 'S' 
    AND CAST(l.log_datetime AS DATE) = @match_date
    AND CAST(a.dh AS DATE) = CAST(l.log_datetime AS DATE) -- Strict Date Match
    AND NOT EXISTS (SELECT 1 FROM bank.rtgs_log_transaction_link WHERE log_id = l.id);

    -- 2. ORPHANS
    INSERT INTO bank.rtgs_orphan_logs (log_id, reason, severity)
    SELECT l.id, 'No match found', 'WARNING'
    FROM bank.rtgs_message_log l
    WHERE CAST(l.log_datetime AS DATE) = @match_date
    AND NOT EXISTS (SELECT 1 FROM bank.rtgs_log_transaction_link WHERE log_id = l.id);

    INSERT INTO bank.rtgs_orphan_transactions (transaction_chrono, transaction_table, reason, severity)
    SELECT e.chrono, 'cpaentrtgss', 'No Log Found', 'WARNING'
    FROM bank.cpaentrtgss e
    WHERE CAST(e.created_date AS DATE) = @match_date
    AND NOT EXISTS (SELECT 1 FROM bank.rtgs_log_transaction_link WHERE transaction_chrono = e.chrono);

    -- 3. SUMMARY
    INSERT INTO bank.rtgs_reconciliation_summary (reconciliation_date, total_logs, total_transactions, matched_logs, matched_transactions, orphan_logs, orphan_transactions, strong_matches, probable_matches, weak_matches, avg_confidence_score)
    SELECT 
        @match_date,
        (SELECT COUNT(*) FROM bank.rtgs_message_log WHERE CAST(log_datetime AS DATE) = @match_date),
        (SELECT COUNT(*) FROM bank.cpaentrtgss WHERE CAST(created_date AS DATE) = @match_date),
        (SELECT COUNT(DISTINCT log_id) FROM bank.rtgs_log_transaction_link WHERE CAST(match_date AS DATE) = @match_date),
        (SELECT COUNT(DISTINCT transaction_chrono) FROM bank.rtgs_log_transaction_link WHERE CAST(match_date AS DATE) = @match_date),
        (SELECT COUNT(*) FROM bank.rtgs_orphan_logs WHERE CAST(created_date AS DATE) = @match_date),
        (SELECT COUNT(*) FROM bank.rtgs_orphan_transactions WHERE CAST(created_date AS DATE) = @match_date),
        (SELECT COUNT(*) FROM bank.rtgs_log_transaction_link WHERE match_level='STRONG'), 0, 0, 0;
END;
GO

-- CLEANUP
IF OBJECT_ID('bank.v_reconciliation_dashboard', 'V') IS NOT NULL DROP VIEW bank.v_reconciliation_dashboard;
IF OBJECT_ID('bank.sprtgs_transactions', 'V') IS NOT NULL DROP VIEW bank.sprtgs_transactions;
IF OBJECT_ID('bank.sprtgs_transactions', 'U') IS NOT NULL DROP TABLE bank.sprtgs_transactions; -- Drop if it was a table
GO

CREATE VIEW bank.sprtgs_transactions AS
SELECT 
    id, 
    'MT103' AS trans_type,
    chrono,
    ref AS reference,
    dco,
    mon AS amount,
    nome1 AS sender,
    nomb1 AS beneficiary,
    dev AS currency,
    created_date,
    CAST(NULL AS INT) AS session_id
FROM bank.sprtgs103
UNION ALL
SELECT 
    t.id, 
    'MT202' AS trans_type,
    t.chrono,
    t.ref AS reference,
    -- Fallback: Use Log Date if Transaction Date is NULL
    ISNULL(t.dco, CAST(l.log_datetime AS DATE)) AS dco,
    t.mon AS amount,
    t.bic AS sender,
    t.etab AS beneficiary,
    t.dev AS currency,
    t.created_date,
    CAST(NULL AS INT) AS session_id
FROM bank.sprtgs202 t
LEFT JOIN bank.rtgs_message_log l ON t.ref = l.field_20; -- Match on Reference to find Date
GO

CREATE VIEW bank.v_reconciliation_dashboard AS
SELECT * FROM bank.rtgs_reconciliation_summary;
GO

PRINT 'DATABASE READY. EXECUTE bank.sp_match_logs_transactions TO RUN MATCHING.';
GO
`;

    let fusionScript = "";

    function generateFusionScript() {
        if (logCount === 0 && unlTableCount === 0) {
            if (!confirm("Aucune donnée importée (Step 1 ou 2 vide). Générer le script structure uniquement ?")) return;
        }

        // 1. Header & Structure
        let s = SQL_SKELETON_TOP;

        // 2. Log Data
        if (logSqlData) {
            s += `\n-- IMPORT: RTGS LOGS (${logCount} rows)\n`;
            s += `SET IDENTITY_INSERT bank.rtgs_message_log ON;\n`;
            s += `INSERT INTO bank.rtgs_message_log (log_datetime, direction, status, ack_type, message_id, bic_cpa, swift_type, field_20, swift_file, input_file, output_file)\nVALUES\n`;
            s += logSqlData + `;\n`;
            s += `SET IDENTITY_INSERT bank.rtgs_message_log OFF;\nGO\n`;
        }

        // 3. UNL Data
        if (unlSqlStatements) {
            s += `\n-- IMPORT: TRANSACTION DATA (${unlTableCount} tables)\n`;
            s += unlSqlStatements;
        }

        // 4. Procedures & Logic
        s += SQL_MATCHING_PROCEDURE;

        fusionScript = s;
        document.getElementById('finalResult').style.display = 'block';
        window.scrollTo(0, document.body.scrollHeight);
    }

    function downloadFusion() {
        const blob = new Blob([fusionScript], { type: 'text/plain' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = "RTGS_FULL_SETUP.sql";
        a.click();
    }

    function openDashboard() {
        if (!fusionScript) return alert("Veuillez d'abord générer le script.");

        const btn = document.querySelector('.btn-analyze[onclick="openDashboard()"]');
        const originalText = btn.innerText;
        btn.innerText = "Transfert en cours...";
        btn.disabled = true;

        // Use IndexedDB for large file support (> 5MB)
        const request = indexedDB.open("RTGS_Analytics_DB", 2);

        request.onupgradeneeded = function (event) {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('transfers')) {
                db.createObjectStore('transfers', { keyPath: 'id' });
            }
        };

        request.onsuccess = function (event) {
            const db = event.target.result;

            // Ensure the store exists before creating transaction
            if (!db.objectStoreNames.contains('transfers')) {
                console.error("Store 'transfers' was not created properly");
                alert("Erreur d'initialisation de la base de données. Veuillez télécharger le fichier manuellement.");
                btn.innerText = originalText;
                btn.disabled = false;
                return;
            }

            const transaction = db.transaction(['transfers'], 'readwrite');
            const store = transaction.objectStore('transfers');

            const putRequest = store.put({
                id: 'pipeline_output',
                content: fusionScript,
                timestamp: Date.now()
            });

            putRequest.onsuccess = function () {
                console.log("SQL Data saved to IndexedDB.");
            };

            putRequest.onerror = function (e) {
                console.error("IndexedDB Write Error", e);
                alert("Erreur: Impossible de transférer les données (Quota dépassé ?). Veuillez télécharger le fichier manuellement.");
                btn.innerText = originalText;
                btn.disabled = false;
            };

            // Wait for transaction to complete before redirecting
            transaction.oncomplete = function () {
                console.log("IndexedDB transaction committed successfully");
                window.location.href = 'index.php?action=dashboard&autoload=1';
            };

            transaction.onerror = function (e) {
                console.error("IndexedDB transaction failed", e);
                alert("Erreur lors du transfert des données. Veuillez télécharger le fichier SQL manuellement.");
                btn.innerText = originalText;
                btn.disabled = false;
            };
        };

        request.onerror = function (e) {
            console.error("IndexedDB Open Error", e);
            alert("Erreur base de données locale. Utilisez le téléchargement manuel.");
            btn.innerText = originalText;
            btn.disabled = false;
        };
    }

    // Navigation
    function goToStep(n) {
        document.querySelectorAll('.step-section').forEach(el => el.classList.remove('active'));
        document.getElementById(`step${n}-section`).classList.add('active');

        // Visuals
        for (let i = 1; i <= 3; i++) {
            document.getElementById(`viz-step-${i}`).classList.remove('active', 'completed');
            if (document.getElementById(`viz-conn-${i}`)) document.getElementById(`viz-conn-${i}`).classList.remove('filled');
        }
        if (n >= 1) document.getElementById('viz-step-1').classList.add(n > 1 ? 'completed' : 'active');
        if (n >= 2) {
            document.getElementById('viz-conn-1').classList.add('filled');
            document.getElementById('viz-step-2').classList.add(n > 2 ? 'completed' : 'active');
        }
        if (n >= 3) {
            document.getElementById('viz-conn-2').classList.add('filled');
            document.getElementById('viz-step-3').classList.add('active');
        }
        window.scrollTo(0, 0);
    }
</script>
<?php require_once __DIR__ . '/footer.php'; ?>