<?php
// templates/import.php
require_once __DIR__ . '/header.php';
?>

<div class="card" style="max-width: 800px; margin: 0 auto;">
    <div style="text-align:center; padding: 40px;">
        <div style="font-size: 3rem; margin-bottom: 20px;">📂</div>
        <h2>Importer des Données SQL</h2>
        <p style="color: #64748b; margin-bottom: 30px;">
            Chargez le fichier `.sql` généré par l'outil ETL (pipe3.html).<br>
            Cela écrasera ou mettra à jour les tables de données.
        </p>

        <?php if (isset($msg)): ?>
            <div style="padding:15px; background:#dcfce7; color:#166534; border-radius:6px; margin-bottom:20px;">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($err)): ?>
            <div style="padding:15px; background:#fee2e2; color:#991b1b; border-radius:6px; margin-bottom:20px;">
                <?= htmlspecialchars($err) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data"
            style="display:flex; flex-direction:column; align-items:center; gap:20px;">
            <input type="file" name="sqlfile" accept=".sql" required
                style="border:2px dashed #cbd5e0; padding:20px; width:100%; border-radius:8px;">
            <button type="submit" class="btn">Lancer l'Importation</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>