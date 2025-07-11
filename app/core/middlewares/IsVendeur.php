<?php

namespace App\Core\Middlewares;

use App\Service\AuthService;

class IsVendeur
{
    public function __invoke(): void
    {
        $authService = new AuthService();
        
        if (!$authService->check()) {
            header('Location: /connexion');
            exit;
        }
        
        if (!$authService->isServiceCommercial()) {
            http_response_code(403);
            echo "<!DOCTYPE html>
            <html>
            <head>
                <title>403 - Accès refusé</title>
                <style>
                    body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                    h1 { color: #d32f2f; }
                    p { color: #666; }
                    a { color: #007bff; text-decoration: none; }
                </style>
            </head>
            <body>
                <h1>403 - Accès refusé</h1>
                <p>Vous n'avez pas les permissions nécessaires pour accéder à cette page.</p>
                <a href='/tableau-de-bord'>Retourner au tableau de bord</a>
            </body>
            </html>";
            exit;
        }
    }
}
