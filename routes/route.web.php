<?php

//route/route.web.php
use App\Core\Router;

// Définition des routes
Router::$routes = [
    // Route par défaut - redirection vers connexion
    "/" => [
        "controller" => "App\\Controller\\ControllerConnection",
        "action" => "index"
    ],
    
    // Routes de connexion
    "/connexion" => [
        "controller" => "App\\Controller\\ControllerConnection",
        "action" => "index"
    ],
    "/connexion/login" => [
        "controller" => "App\\Controller\\ControllerConnection",
        "action" => "login"
    ],
    "/deconnexion" => [
        "controller" => "App\\Controller\\ControllerConnection",
        "action" => "logout"
    ],
    
    // Routes de création de compte
    "/creer-compte" => [
        "controller" => "App\\Controller\\ControllerCreerComptePrincipal",
        "action" => "index"
    ],
    "/creer-compte/store" => [
        "controller" => "App\\Controller\\ControllerCreerComptePrincipal",
        "action" => "store"
    ],
    
    // Routes du tableau de bord
    "/tableau-de-bord" => [
        "controller" => "App\\Controller\\ControllerTableauDeBord",
        "action" => "index",
        "middleware" => ["auth"]
    ],
    
    // Routes des transactions
    "/transactions" => [
        "controller" => "App\\Controller\\TransactionController",
        "action" => "index",
        "middleware" => ["auth"]
    ],
    
    // Routes des comptes
    "/comptes" => [
        "controller" => "App\\Controller\\CompteController",
        "action" => "index",
        "middleware" => ["auth"]
    ]
];

// Le routeur sera appelé depuis index.php
