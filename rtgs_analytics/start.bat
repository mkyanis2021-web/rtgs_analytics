@echo off
echo Démarrage du serveur RTGS Analytics...
echo Ouvrez votre navigateur sur http://localhost:8080
echo Appuyez sur Ctrl+C pour arrêter le serveur.
php -S localhost:8080 -t public
