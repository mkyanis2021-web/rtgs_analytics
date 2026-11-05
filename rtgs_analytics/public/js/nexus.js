/* public/js/nexus.js */

async function applyFilters() {
    const swift = document.getElementById('swiftFilter').value;
    const minConf = document.getElementById('confidenceRange').value;
    const status = document.getElementById('statusFilter').value;
    const dateStart = document.getElementById('dateFilter').value;

    const body = new FormData();
    body.append('swift_type', swift);
    body.append('min_confidence', minConf);
    body.append('status', status);
    body.append('date_start', dateStart);

    // Show loading state? 
    document.getElementById('gridBody').innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;">Loading data...</td></tr>';

    try {
        const response = await fetch('index.php?action=nexus_api', {
            method: 'POST',
            body: body
        });

        const data = await response.json();
        renderGrid(data);
    } catch (e) {
        console.error(e);
        document.getElementById('gridBody').innerHTML = '<tr><td colspan="7" style="color:red; text-align:center;">Error loading data</td></tr>';
    }
}

async function loadStats() {
    try {
        const response = await fetch('index.php?action=nexus_api'); // GET for stats
        const stats = await response.json();
        document.getElementById('totalLogs').innerText = stats.total_logs;
        document.getElementById('matchedCount').innerText = stats.matched;
        document.getElementById('orphanCount').innerText = stats.orphans;
    } catch (e) {
        console.error("Stats Error", e);
    }
}

function renderGrid(data) {
    const container = document.getElementById('gridBody');
    if (!data || data.length === 0) {
        container.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;">No records found matching criteria.</td></tr>';
        return;
    }

    container.innerHTML = data.map(row => {
        let confClass = '';
        if (row.confidence_score >= 80) confClass = 'match-high';
        else if (row.confidence_score < 50 && row.confidence_score != null) confClass = 'match-low';

        let matchDisplay = row.match_level || '<span class="badge-orphan">ORPHAN</span>';
        let confDisplay = row.confidence_score != null ? row.confidence_score + '%' : '-';

        let amtDisplay = row.amount ? new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'DZD' }).format(row.amount) : '-';

        return `
        <tr>
            <td>${row.log_datetime}</td>
            <td style="font-family:monospace; color:#a5d6ff;">${row.log_ref}</td>
            <td><span style="color:#79c0ff">MT${row.swift_type}</span></td>
            <td>${row.status === 'ACK' ? '<span style="color:#7ee787">ACK</span>' : '<span style="color:#ffa657">' + row.status + '</span>'}</td>
            <td>${matchDisplay}</td>
            <td class="${confClass}">
                ${confDisplay}
            </td>
            <td style="font-family:monospace; font-weight:bold;">${amtDisplay}</td>
        </tr>
    `;
    }).join('');
}

// Initial Load
window.onload = function () {
    loadStats();
    applyFilters();
};
