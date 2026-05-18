<?php

namespace App\Core;

class Request
{
    public static function body(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

        if (str_contains($contentType, 'application/json')) {
            $data = json_decode(file_get_contents('php://input'), true);
            return is_array($data) ? $data : [];
        }

        return $_POST;
    }

    public static function input(string $key, $default = null)
    {
        $data = self::body();
        return $data[$key] ?? $default;
    }

    public static function all(): array
    {
        return self::body();
    }

    public static function header(string $name): ?string
    {
        $header = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $_SERVER[$header] ?? null;
    }
}
