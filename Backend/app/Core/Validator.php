<?php

namespace App\Core;

class Validator
{
    public static function required(string $value): bool
    {
        return trim($value) !== '';
    }

    public static function maxLength(string $value, int $length): bool
    {
        return mb_strlen($value) <= $length;
    }

    public static function username(string $value): bool
    {
        return (bool)preg_match('/^[A-Za-z][A-Za-z0-9_]{2,19}$/', $value);
    }

    public static function email(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function password(string $value): bool
    {
        return strlen($value) >= 8;
    }

    public static function strongPassword(string $value): bool
    {
        return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $value) === 1;
    }

    public static function url(string $value): bool
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    public static function category(string $value): bool
    {
        $normalized = strtolower(str_replace([' ', '_'], '', trim($value)));
        return in_array($normalized, ['fiction', 'nonfiction', 'non-fiction', 'nonfiction'], true) || $normalized === 'nonfiction';
    }
}
