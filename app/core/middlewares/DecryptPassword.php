<?php

namespace App\Core\Middlewares;

class DecryptPassword
{
    public function __invoke(): void
    {
        // Ce middleware gère la vérification des mots de passe lors de l'authentification
        // Il ne "décrypte" pas vraiment (car on utilise un hash à sens unique)
        // Mais il fournit les outils de vérification
    }

    /**
     * Vérifier un mot de passe contre son hash
     */
    public static function verifyPassword(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }

    /**
     * Vérifier si un hash de mot de passe doit être re-hashé
     * (utile lors de changements d'algorithme ou de coût)
     */
    public static function needsRehash(string $hashedPassword): bool
    {
        return password_needs_rehash($hashedPassword, PASSWORD_DEFAULT);
    }

    /**
     * Obtenir les informations sur un hash de mot de passe
     */
    public static function getPasswordInfo(string $hashedPassword): array
    {
        return password_get_info($hashedPassword);
    }

    /**
     * Vérifier si une chaîne est un hash de mot de passe valide
     */
    public static function isValidHash(string $hash): bool
    {
        $info = password_get_info($hash);
        return $info['algo'] !== null;
    }

    /**
     * Comparer deux mots de passe en texte clair de manière sécurisée
     * (pour la confirmation de mot de passe)
     */
    public static function comparePasswords(string $password1, string $password2): bool
    {
        return hash_equals($password1, $password2);
    }

    /**
     * Générer un token de réinitialisation de mot de passe
     */
    public static function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Vérifier l'âge d'un mot de passe (utile pour forcer le changement périodique)
     */
    public static function isPasswordExpired(string $hashedPassword, int $maxAgeInDays = 90): bool
    {
        // Cette fonction nécessiterait une base de données pour stocker les dates de création
        // Pour l'instant, on retourne false
        // TODO: Implémenter la vérification d'âge avec timestamp en base
        return false;
    }

    /**
     * Analyser la force d'un mot de passe
     */
    public static function analyzePasswordStrength(string $password): array
    {
        $score = 0;
        $feedback = [];

        // Longueur
        if (strlen($password) >= 8) {
            $score += 1;
        } else {
            $feedback[] = 'Trop court (minimum 8 caractères)';
        }

        if (strlen($password) >= 12) {
            $score += 1;
        }

        // Complexité
        if (preg_match('/[a-z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Manque de minuscules';
        }

        if (preg_match('/[A-Z]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Manque de majuscules';
        }

        if (preg_match('/[0-9]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Manque de chiffres';
        }

        if (preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $score += 1;
        } else {
            $feedback[] = 'Manque de caractères spéciaux';
        }

        // Motifs courants à éviter
        if (preg_match('/(.)\1{2,}/', $password)) {
            $score -= 1;
            $feedback[] = 'Évitez les répétitions de caractères';
        }

        if (preg_match('/123|abc|qwerty|password/i', $password)) {
            $score -= 2;
            $feedback[] = 'Évitez les séquences communes';
        }

        // Déterminer le niveau
        $level = 'Très faible';
        if ($score >= 6) {
            $level = 'Très fort';
        } elseif ($score >= 5) {
            $level = 'Fort';
        } elseif ($score >= 4) {
            $level = 'Moyen';
        } elseif ($score >= 2) {
            $level = 'Faible';
        }

        return [
            'score' => max(0, $score),
            'level' => $level,
            'feedback' => $feedback,
            'isSecure' => $score >= 4
        ];
    }

    /**
     * Masquer un mot de passe pour l'affichage (utile pour les logs)
     */
    public static function maskPassword(string $password, int $visibleChars = 2): string
    {
        $length = strlen($password);
        if ($length <= $visibleChars * 2) {
            return str_repeat('*', $length);
        }
        
        $start = substr($password, 0, $visibleChars);
        $end = substr($password, -$visibleChars);
        $middle = str_repeat('*', $length - $visibleChars * 2);
        
        return $start . $middle . $end;
    }
}
