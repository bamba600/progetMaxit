<?php

// Affichage des erreurs pour le développement
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    require_once __DIR__ . '/../app/config/bootstrap.php';
    App\Core\Router::resolve();
    
} catch (Exception $e) {
    echo "Erreur lors du chargement de l'application : " . $e->getMessage();
    echo "\n<br>Trace : " . $e->getTraceAsString();
} catch (Error $e) {
    echo "Erreur fatale : " . $e->getMessage();
    echo "\n<br>Trace : " . $e->getTraceAsString();
}
