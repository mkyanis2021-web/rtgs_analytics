<?php
// templates/admin_users.php
require_once __DIR__ . '/header.php';
?>

<div class="card">
    <h2>👤 Gestion des Utilisateurs</h2>

    <?php if (isset($msg)): ?>
        <p style="color:green; background:#dcfce7; padding:10px; border-radius:4px;">
            <?= htmlspecialchars($msg) ?>
        </p>
    <?php endif; ?>
    <?php if (isset($err)): ?>
        <p style="color:red; background:#fee2e2; padding:10px; border-radius:4px;">
            <?= htmlspecialchars($err) ?>
        </p>
    <?php endif; ?>

    <!-- ADD USER FORM -->
    <form method="POST" action="index.php?action=admin_users"
        style="display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap:15px; align-items:end; margin-bottom:30px; padding-bottom:20px; border-bottom:1px solid #e2e8f0;">
        <div class="form-g">
            <label>Nom d'utilisateur</label>
            <input type="text" name="username" class="form-control" required placeholder="ex: cpa_auditor">
        </div>
        <div class="form-g">
            <label>Mot de passe</label>
            <input type="password" name="password" class="form-control" required placeholder="******">
        </div>
        <div class="form-g">
            <label>Rôle</label>
            <select name="role_id" class="form-control" required>
                <?php foreach ($roles as $r): ?>
                    <option value="<?= $r['id'] ?>">
                        <?= htmlspecialchars($r['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-g">
            <button type="submit" class="btn" style="height:42px;">+ Créer Utilisateur</button>
        </div>
    </form>

    <!-- USERS LIST -->
    <h3>Liste des Utilisateurs</h3>
    <table style="width:100%; border-collapse:collapse; text-align:left;">
        <thead style="background:#f1f5f9; border-bottom:2px solid #e2e8f0;">
            <tr>
                <th style="padding:10px;">ID</th>
                <th style="padding:10px;">Username</th>
                <th style="padding:10px;">Rôle</th>
                <th style="padding:10px;">Créé le</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usersList as $u): ?>
                <tr style="border-bottom:1px solid #f1f5f9;">
                    <td style="padding:10px;">
                        <?= $u['id'] ?>
                    </td>
                    <td style="padding:10px; font-weight:bold;">
                        <?= htmlspecialchars($u['username']) ?>
                    </td>
                    <td style="padding:10px;">
                        <span class="tag tag-blue">
                            <?= htmlspecialchars($u['role_name']) ?>
                        </span>
                    </td>
                    <td style="padding:10px;">
                        <?= $u['created_at'] ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>