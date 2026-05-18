<?php

namespace App\Core;

class Cookie
{
    public static function set(string $name, string $value, int $days = 30): void
    {
        $expires = time() + ($days * 86400);
        $secure = self::secure();

        setcookie($name, $value, [
            'expires' => $expires,
            'path' => BASE_PATH ?: '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }

    public static function get(string $name): ?string
    {
        return $_COOKIE[$name] ?? null;
    }

    public static function delete(string $name): void
    {
        setcookie($name, '', [
            'expires' => time() - 3600,
            'path' => BASE_PATH ?: '/',
            'secure' => self::secure(),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE[$name]);
    }

    private static function secure(): bool
    {
        return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    }
}
