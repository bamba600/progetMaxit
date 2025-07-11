<?php

namespace App\Config;

use App\Core\Middlewares\Auth;
use App\Core\Middlewares\IsVendeur;
use App\Core\Middlewares\CryptPassword;
use App\Core\Middlewares\DecryptPassword;

class Middleware
{
    public static array $middlewares = [
        "auth" => Auth::class,
        "isVendeur" => IsVendeur::class,
        "cryptPassword" => CryptPassword::class,
        "decryptPassword" => DecryptPassword::class
    ];

    public static function get(string $name): ?string
    {
        return self::$middlewares[$name] ?? null;
    }

    public static function exists(string $name): bool
    {
        return isset(self::$middlewares[$name]);
    }

    public static function register(string $name, string $className): void
    {
        self::$middlewares[$name] = $className;
    }
}