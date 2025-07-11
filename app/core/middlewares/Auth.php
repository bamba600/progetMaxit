<?php

namespace App\Core\Middlewares;

use App\Core\App;
use App\Service\AuthService;

class Auth
{
    public function __invoke(): void
    {
        $authService = new AuthService();
        
        if (!$authService->check()) {
            // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
            header('Location: /connexion');
            exit;
        }
        
        // Vérifier si la session n'a pas expiré
        $authService->checkSessionExpiry();
    }
}





