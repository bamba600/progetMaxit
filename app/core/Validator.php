<?php
namespace App\Core;

class Validator
{
    private static array $errors = [];
    private static array $rules = [];

    /**
     * Initialiser les règles de validation
     */
    private static function initRules(): void
    {
        if (empty(self::$rules)) {
            self::$rules = [
                'required' => function($value) {
                    return !empty(trim($value));
                },
                'email' => function($value) {
                    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
                },
                'phone' => function($value) {
                    return preg_match('/^[0-9+\-\s()]+$/', $value) === 1;
                },
                'phone_senegal' => function($value) {
                    // Format sénégalais : 77, 78, 76, 75, 70 suivi de 7 chiffres
                    // ou 33 suivi de 7 chiffres (fixe)
                    return preg_match('/^(77|78|76|75|70|33)[0-9]{7}$/', $value) === 1;
                },
                'cni_senegal' => function($value) {
                    // CNI sénégalaise : 13 chiffres 
                    // Format : XXXXXXXXXXX (11 à 13 chiffres)
                    return preg_match('/^[0-9]{11,13}$/', $value) === 1;
                },
                'name' => function($value) {
                    return preg_match('/^[a-zA-ZÀ-ÿ\s\-\']{2,50}$/', $value) === 1;
                },
                'login' => function($value) {
                    return preg_match('/^[a-zA-Z0-9_\-]{3,20}$/', $value) === 1;
                },
                'password' => function($value) {
                    return strlen($value) >= 8;
                },
                'password_strict' => function($value) {
                    return strlen($value) >= 8 && 
                           preg_match('/[A-Z]/', $value) && 
                           preg_match('/[a-z]/', $value) && 
                           preg_match('/[0-9]/', $value);
                },
                'min_length' => function($value, $min) {
                    return strlen(trim($value)) >= $min;
                },
                'max_length' => function($value, $max) {
                    return strlen(trim($value)) <= $max;
                },
                'numeric' => function($value) {
                    return is_numeric($value);
                },
                'amount' => function($value) {
                    return is_numeric($value) && $value > 0 && $value <= 1000000;
                },
                'unique_phone' => function($value, $service) {
                    // Vérifier que le numéro n'existe pas déjà
                    return !$service->phoneExists($value);
                },
                'unique_cni' => function($value, $service) {
                    // Vérifier que la CNI n'existe pas déjà
                    return !$service->cniExists($value);
                },
                'unique_login' => function($value, $service) {
                    // Vérifier que le login n'existe pas déjà
                    return !$service->loginExists($value);
                }
            ];
        }
    }

    /**
     * Ajouter une erreur
     */
    public static function addError($field, $message)
    {
        self::$errors[$field][] = $message;
    }

    /**
     * Obtenir toutes les erreurs
     */
    public static function getErrors(): array
    {
        return self::$errors;
    }

    /**
     * Vider les erreurs
     */
    public static function clearErrors(): void
    {
        self::$errors = [];
    }

    /**
     * Ajouter une règle personnalisée
     */
    public static function addRule(string $name, callable $rule): void
    {
        self::initRules();
        self::$rules[$name] = $rule;
    }

    /**
     * Fonction principale de validation
     */
    public static function validate(array $data, array $rules): bool
    {
        self::initRules();
        self::clearErrors();

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? '';
            
            foreach ($fieldRules as $rule) {
                $ruleName = '';
                $ruleParams = [];
                $message = null;

                // Traiter différents formats de règles
                if (is_string($rule)) {
                    // Format simple : 'required'
                    $ruleName = $rule;
                } elseif (is_array($rule)) {
                    // Format avec paramètres : ['unique_phone', $service]
                    $ruleName = $rule[0] ?? '';
                    $ruleParams = array_slice($rule, 1);
                } else {
                    // Format non supporté
                    continue;
                }

                // Vérifier si la règle existe
                if (!isset(self::$rules[$ruleName])) {
                    continue;
                }

                // Appliquer la règle
                $ruleFunction = self::$rules[$ruleName];
                $isValid = false;

                if (empty($ruleParams)) {
                    $isValid = $ruleFunction($value);
                } else {
                    $isValid = $ruleFunction($value, ...$ruleParams);
                }

                // Si la validation échoue, ajouter l'erreur
                if (!$isValid) {
                    $defaultMessage = self::getDefaultMessage($ruleName, $field, $ruleParams);
                    self::addError($field, $message ?? $defaultMessage);
                }
            }
        }

        return empty(self::$errors);
    }

    /**
     * Obtenir un message d'erreur par défaut
     */
    private static function getDefaultMessage(string $rule, string $field, array $params = []): string
    {
        // Filtrer les paramètres pour ne garder que les valeurs simples (pas les objets)
        $simpleParams = array_filter($params, function($param) {
            return !is_object($param);
        });
        
        $messages = [
            'required' => "Le champ {$field} est requis",
            'email' => "Le champ {$field} doit être un email valide",
            'phone' => "Le champ {$field} doit être un numéro de téléphone valide",
            'phone_senegal' => "Le champ {$field} doit être un numéro de téléphone sénégalais valide (77xxxxxxx, 78xxxxxxx, 76xxxxxxx, 75xxxxxxx, 70xxxxxxx ou 33xxxxxxx)",
            'cni_senegal' => "Le champ {$field} doit être un numéro de CNI sénégalais valide (11 à 13 chiffres)",
            'unique_phone' => "Ce numéro de téléphone est déjà utilisé",
            'unique_cni' => "Ce numéro de CNI est déjà utilisé",
            'unique_login' => "Ce login est déjà utilisé",
            'name' => "Le champ {$field} doit être un nom valide",
            'login' => "Le champ {$field} doit être un login valide",
            'password' => "Le champ {$field} doit contenir au moins 8 caractères",
            'password_strict' => "Le champ {$field} doit contenir au moins 8 caractères, une majuscule, une minuscule et un chiffre",
            'min_length' => "Le champ {$field} doit contenir au moins " . ($simpleParams[0] ?? 'X') . " caractères",
            'max_length' => "Le champ {$field} ne peut pas dépasser " . ($simpleParams[0] ?? 'X') . " caractères",
            'numeric' => "Le champ {$field} doit être numérique",
            'amount' => "Le champ {$field} doit être un montant valide"
        ];

        return $messages[$rule] ?? "Le champ {$field} est invalide";
    }

    // ===== MÉTHODES DE COMPATIBILITÉ (pour l'ancien code) =====

    /**
     * Valider un email (compatibilité)
     */
    public static function isValidEmail($email): bool
    {
        self::initRules();
        return self::$rules['email']($email);
    }

    /**
     * Valider un téléphone (compatibilité)
     */
    public static function isValidPhone(string $phone): bool
    {
        self::initRules();
        return self::$rules['phone']($phone);
    }

    /**
     * Valider un mot de passe (compatibilité)
     */
    public static function isValidPassword($password): bool
    {
        self::initRules();
        return self::$rules['password']($password);
    }

    /**
     * Valider un nom d'utilisateur (compatibilité)
     */
    public static function isValidUsername($username): bool
    {
        self::initRules();
        return self::$rules['login']($username);
    }

    /**
     * Valider un login (compatibilité)
     */
    public static function isValidLogin(string $login): bool
    {
        self::initRules();
        return self::$rules['login']($login);
    }

    /**
     * Valider un nom (compatibilité)
     */
    public static function isValidName(string $name): bool
    {
        self::initRules();
        return self::$rules['name']($name);
    }

    /**
     * Vérifier si non vide (compatibilité)
     */
    public static function isNotEmpty($value): bool
    {
        self::initRules();
        return self::$rules['required']($value);
    }

    /**
     * Valider un montant (compatibilité)
     */
    public static function isValidAmount(float $amount): bool
    {
        self::initRules();
        return self::$rules['amount']($amount);
    }

    /**
     * Nettoyer une chaîne
     */
    public static function sanitizeString(string $input): string
    {
        return trim(htmlspecialchars($input, ENT_QUOTES, 'UTF-8'));
    }

    /**
     * Valider un fichier uploadé
     */
    public static function validateFile(array $file, array $allowedTypes = [], int $maxSize = 2097152): array
    {
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Erreur lors de l\'upload du fichier';
            return $errors;
        }

        if ($file['size'] > $maxSize) {
            $errors[] = 'Le fichier est trop volumineux (max: ' . ($maxSize / 1024 / 1024) . ' MB)';
        }

        if (!empty($allowedTypes)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mimeType, $allowedTypes)) {
                $errors[] = 'Type de fichier non autorisé';
            }
        }

        return $errors;
    }

    /**
     * Valider un mot de passe avec critères stricts (compatibilité)
     */
    public static function validatePasswordStrict(string $password): array
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

        return $errors;
    }

    /**
     * Méthode de validation de formulaire (ancienne version - dépréciée)
     */
    public static function validateFormData(array $data, array $rules): array
    {
        // Rediriger vers la nouvelle méthode validate
        self::validate($data, $rules);
        return self::getErrors();
    }
}