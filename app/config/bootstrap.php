<?php
use App\Core\App;
use App\Core\Session;
use App\Core\Database;

require_once __DIR__ . '/../../vendor/autoload.php';

// Charger la configuration des middlewares
require_once __DIR__ . '/middlewares.php';

// Charger les fonctions d'aide
require_once __DIR__ . '/helpers.php';

// Charger les variables d'environnement
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Définir des constantes à partir du .env
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_DRIVER', $_ENV['DB_DRIVER']);
define('AUTH_URL', $_ENV['AUTH_URL']);

// Enregistrer les dépendances principales
App::setDependency('session', Session::getInstance());
App::setDependency('db', Database::getConnection());

// Charger les routes
require_once __DIR__ . '/../../routes/route.web.php';