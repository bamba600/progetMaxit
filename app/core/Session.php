<?php


namespace App\Core;

class Session
{
    private static ?Session $instance = null;

    private function __construct()
    {
        if (php_sapi_name() === 'cli') {
            // En mode CLI, ne pas démarrer de session
            return;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function getInstance(): Session
    {
        if (self::$instance === null) {
            self::$instance = new Session();
        }
        return self::$instance;
    }

    public function set(string $key, $data): void
    {
        $_SESSION[$key] = $data;
    }

    public function get(string $key)
    {
        return $_SESSION[$key] ?? null;
    }

    public function unset(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function isset(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function destroy(): void
    {
        if (php_sapi_name() === 'cli') {
            // En mode CLI, vider simplement le tableau $_SESSION
            $_SESSION = [];
        } else {
            // En mode web, utiliser les fonctions de session
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_unset();
                session_destroy();
            }
        }
    }
}