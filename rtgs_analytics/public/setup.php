<?php
// public/setup.php
require_once __DIR__ . '/../config/db.php';

echo "<h1>🛠️ RTGS Analytics - Initialisation de la Base de Données</h1>";

try {
    $sqlFile = __DIR__ . '/../sql/demo_setup.sql';
    if (!file_exists($sqlFile)) {
        die("❌ Erreur : Fichier SQL introuvable ($sqlFile)");
    }

    $sql = file_get_contents($sqlFile);

    // Split by semicolon? Or just run if driver allows multiple statements.
    // PDO::exec sometimes only runs the first statement if not configured for multi-queries.
    // Safest is to try splitting, or assume MySQL driver handles it if formatted correctly.
    // Since our SQL file is clean, we can try running it.

    // Attempting to run full script
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1); // Enable emulation for multi-query
    $pdo->exec($sql);

    echo "<div style='color: green; border: 1px solid green; padding: 20px; background: #f0fdf4; border-radius: 8px;'>";
    echo "<h2>✅ Succès !</h2>";
    echo "<p>La base de données a été restaurée avec les données de démonstration.</p>";
    echo "<p><strong>Utilisateur :</strong> admin<br><strong>Mot de passe :</strong> admin123</p>";
    echo "<p><a href='index.php?action=login' style='display: inline-block; margin-top: 10px; padding: 10px 20px; background: #2563eb; color: white; text-decoration: none; border-radius: 5px;'>➡️ Accéder à la connexion</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<div style='color: red; border: 1px solid red; padding: 20px; background: #fef2f2; border-radius: 8px;'>";
    echo "<h2>❌ Erreur SQL</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}
?>