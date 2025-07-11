<?php

namespace App\Core;

class App 
{
    private static array $dependencies = [];

    public static function setDependency(string $name, $instance): void
    {
        self::$dependencies[$name] = $instance;
    }

    public static function getDependency(string $name)
    {
        return self::$dependencies[$name] ?? null;
    }

    public static function getAllDependencies(): array
    {
        return self::$dependencies;
    }
}