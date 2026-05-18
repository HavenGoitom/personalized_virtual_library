<?php

namespace App\Core;

use App\Core\Cookie;
use App\Core\Session;
use App\Models\AuthToken;
use App\Models\User;

class Auth
{
    private const REMEMBER_COOKIE = 'wl_remember';

    public static function login(array $user, bool $remember = false): void
    {
        Session::start();
        Session::set('user_id', $user['id']);

        if ($remember) {
            $config = require __DIR__ . '/../../config/app.php';
            $token = (new AuthToken())->createToken($user['id']);
            Cookie::set(self::REMEMBER_COOKIE, $token, $config['remember_days']);
        }
    }

    public static function user(): ?array
    {
        Session::start();

        $userId = Session::get('user_id');
        if ($userId) {
            return (new User())->findById($userId);
        }

        $remember = Cookie::get(self::REMEMBER_COOKIE);
        if (!$remember) {
            return null;
        }

        if (str_contains($remember, ':')) {
            [$selector, $validator] = explode(':', $remember, 2);
            $token = (new AuthToken())->findValidTokenBySelector($selector);

            if (!$token || !password_verify($validator, $token['validator_hash'])) {
                Cookie::delete(self::REMEMBER_COOKIE);
                return null;
            }
        } else {
            $token = (new AuthToken())->findValidTokenByToken($remember);

            if (!$token) {
                Cookie::delete(self::REMEMBER_COOKIE);
                return null;
            }
        }

        $user = (new User())->findById((int)$token['user_id']);
        if (!$user) {
            Cookie::delete(self::REMEMBER_COOKIE);
            return null;
        }

        Session::set('user_id', $user['id']);
        return $user;
    }

    public static function check(): bool
    {
        return (bool)self::user();
    }

    public static function logout(): void
    {
        Session::start();
        $remember = Cookie::get(self::REMEMBER_COOKIE);

        if ($remember) {
            if (str_contains($remember, ':')) {
                [$selector] = explode(':', $remember, 2);
                (new AuthToken())->revokeTokenBySelector($selector);
            } else {
                (new AuthToken())->revokeTokenByToken($remember);
            }
        }

        Cookie::delete(self::REMEMBER_COOKIE);
        Session::remove('user_id');
        session_unset();
        session_destroy();
    }
}
