<?php
// templates/activity_logs.php
require_once __DIR__ . '/header.php';
?>

<div class="card">
    <h2>📜 Historique & Audit</h2>

    <div style="display:flex; gap:20px; flex-wrap:wrap;">
        <!-- USER LOGS -->
        <div style="flex:1; min-width:300px;">
            <h3>Activités Utilisateurs</h3>
            <div style="max-height:500px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px;">
                <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                    <thead style="background:#f8fafc; position:sticky; top:0;">
                        <tr>
                            <th style="padding:8px; text-align:left;">Date</th>
                            <th style="padding:8px; text-align:left;">Utilisateur</th>
                            <th style="padding:8px; text-align:left;">Action</th>
                            <th style="padding:8px; text-align:left;">Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($userLogs as $log): ?>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:8px; color:#64748b;">
                                    <?= $log['created_at'] ?>
                                </td>
                                <td style="padding:8px; font-weight:600;">
                                    <?= htmlspecialchars($log['username']) ?>
                                </td>
                                <td style="padding:8px;">
                                    <span class="tag tag-blue">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td style="padding:8px; color:#334155;">
                                    <?= htmlspecialchars($log['details']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ETL LOGS -->
        <div style="flex:1; min-width:300px;">
            <h3>Compte-rendu ETL</h3>
            <div style="max-height:500px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:6px;">
                <table style="width:100%; border-collapse:collapse; font-size:0.85rem;">
                    <thead style="background:#f8fafc; position:sticky; top:0;">
                        <tr>
                            <th style="padding:8px; text-align:left;">Date</th>
                            <th style="padding:8px; text-align:left;">Statut</th>
                            <th style="padding:8px; text-align:left;">Message</th>
                            <th style="padding:8px; text-align:left;">Recs</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($etlLogs as $log): ?>
                            <tr style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:8px; color:#64748b;">
                                    <?= $log['created_at'] ?>
                                </td>
                                <td style="padding:8px;">
                                    <?php if ($log['status'] === 'SUCCESS'): ?>
                                        <span class="tag tag-green">SUCCÈS</span>
                                    <?php elseif ($log['status'] === 'FAILED'): ?>
                                        <span class="tag tag-red">ÉCHEC</span>
                                    <?php else: ?>
                                        <span class="tag tag-orange">
                                            <?= htmlspecialchars($log['status']) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:8px; font-size:0.8rem;">
                                    <?= htmlspecialchars($log['message']) ?>
                                </td>
                                <td style="padding:8px; font-weight:bold;">
                                    <?= $log['records_processed'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>