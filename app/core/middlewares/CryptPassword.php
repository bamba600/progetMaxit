<?php

namespace App\Core\Middlewares;

class CryptPassword
{
    public function __invoke(): void
    {
        // Ce middleware crypte automatiquement les mots de passe dans les données POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->cryptPasswords($_POST);
        }
    }

    /**
     * Crypter les mots de passe dans les données
     */
    private function cryptPasswords(array &$data): void
    {
        $passwordFields = ['mot_de_passe', 'password', 'motDePasse', 'new_password', 'nouveau_mot_de_passe'];
        
        foreach ($passwordFields as $field) {
            if (isset($data[$field]) && !empty($data[$field])) {
                // Vérifier si le mot de passe n'est pas déjà hashé
                if (!$this->isAlreadyHashed($data[$field])) {
                    $data[$field] = $this->hashPassword($data[$field]);
                }
            }
        }
        
        // Mettre à jour $_POST avec les mots de passe cryptés
        $_POST = $data;
    }

    /**
     * Hasher un mot de passe
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Vérifier si un mot de passe est déjà hashé
     */
    private function isAlreadyHashed(string $password): bool
    {
        $info = password_get_info($password);
        return $info['algo'] !== null;
    }

    /**
     * Valider la force d'un mot de passe avant cryptage
     */
    public static function validatePasswordStrength(string $password): array
    {
        $errors = [];

        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule';
        }

        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule';
        }

        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre';
        }

        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un caractère spécial';
        }

        return $errors;
    }

    /**
     * Générer un mot de passe aléatoire sécurisé
     */
    public static function generateSecurePassword(int $length = 12): string
    {
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*(),.?":{}|<>';
        
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];
        
        $allChars = $lowercase . $uppercase . $numbers . $symbols;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }
        
        return str_shuffle($password);
    }
}
