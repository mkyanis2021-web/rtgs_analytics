<?php
// templates/sql_tool.php
require_once __DIR__ . '/header.php';
?>

<div class="card">
    <h2>🛠️ Import & Exécution SQL</h2>

    <?php if (isset($msg)): ?>
        <p style="color:green; background:#dcfce7; padding:10px; border-radius:4px;">
            <?= htmlspecialchars($msg) ?> <!-- Allow raw HTML if safe, but here plain text is safer -->
        </p>
    <?php endif; ?>
    <?php if (isset($err)): ?>
        <p style="color:red; background:#fee2e2; padding:10px; border-radius:4px;">
            <?= htmlspecialchars($err) ?>
        </p>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px;">

        <!-- IMPORT FORM -->
        <div style="background:#f8fafc; padding:20px; border-radius:8px; border:1px solid #e2e8f0;">
            <h3>1. Charger un fichier SQL</h3>
            <form method="POST" action="index.php?action=sql_tool" enctype="multipart/form-data">
                <input type="file" name="sql_file" accept=".sql" required class="form-control"
                    style="margin-bottom:10px; width:100%;">
                <button type="submit" class="btn" style="width:100%;">📤 Charger le fichier</button>
            </form>
        </div>

        <!-- EXECUTION CONFIRMATION -->
        <div style="background:#fff; padding:20px; border-radius:8px; border:1px solid #e2e8f0;">
            <h3>2. Réviser & Exécuter</h3>
            <form method="POST" action="index.php?action=sql_tool">
                <textarea name="sql_content" rows="10" class="form-control"
                    style="width:100%; font-family:monospace; font-size:0.8rem; background:#1e293b; color:#fbbf24; margin-bottom:10px;"
                    placeholder="Le contenu SQL apparaîtra ici après chargement..."><?= htmlspecialchars($viewSql) ?></textarea>
                <!-- Hidden inputs to carry over context if needed, mostly redundant if content is in textarea -->
                <button type="submit" name="execute_sql" value="1" class="btn" style="background:#dc2626; width:100%;"
                    onclick="return confirm('⚠️ Êtes-vous sûr de vouloir exécuter ce script SQL ? Cette action est irréversible.');">
                    🚀 Exécuter le SQL
                </button>
            </form>
        </div>
    </div>

    <!-- HISTORY -->
    <h3 style="margin-top:40px;">📚 Historique des Imports</h3>
    <table style="width:100%; border-collapse:collapse; text-align:left;">
        <thead style="background:#f1f5f9; border-bottom:2px solid #e2e8f0;">
            <tr>
                <th style="padding:10px;">Date</th>
                <th style="padding:10px;">Utilisateur</th>
                <th style="padding:10px;">Fichier</th>
                <th style="padding:10px;">Statut</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $h): ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:10px;">
                        <?= $h['created_at'] ?>
                    </td>
                    <td style="padding:10px; font-weight:bold;">
                        <?= htmlspecialchars($h['username']) ?>
                    </td>
                    <td style="padding:10px;">
                        <?= htmlspecialchars($h['filename']) ?>
                    </td>
                    <td style="padding:10px;">
                        <?php if ($h['executed_at']): ?>
                            <span class="tag tag-green">EXÉCUTÉ</span>
                            <small class="text-muted">
                                <?= $h['executed_at'] ?>
                            </small>
                        <?php else: ?>
                            <span class="tag tag-blue">CHARGÉ</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>