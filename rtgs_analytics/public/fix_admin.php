<?php
// public/fix_admin.php
require_once __DIR__ . '/../config/db.php';

// On génère le bon hash PHP pour "admin123"
$hash = password_hash('admin123', PASSWORD_DEFAULT);

try {
    // On met à jour l'utilisateur admin dans la base de données
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE username = 'admin'");
    $stmt->execute([$hash]);
    
    echo "<h2 style='color: green;'>✅ Mot de passe de l'admin mis à jour avec succès !</h2>";
    echo "<a href='index.php?action=login'>Retour à la connexion</a>";
} catch (Exception $e) {
    echo "<h2 style='color: red;'>❌ Erreur : " . $e->getMessage() . "</h2>";
}
?>