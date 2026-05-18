<?php

namespace App\Models;

use App\Core\Model;

class AuthToken extends Model
{
    public function createToken(int $userId): string
    {
        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $validatorHash = password_hash($validator, PASSWORD_BCRYPT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        $stmt = $this->db->prepare('INSERT INTO auth_tokens (user_id, selector, validator_hash, expires_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $selector, $validatorHash, $expiresAt]);

        return $selector . ':' . $validator;
    }

    public function findValidToken(string $selector): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM auth_tokens WHERE selector = ? AND revoked_at IS NULL AND expires_at > NOW()');
        $stmt->execute([$selector]);
        $token = $stmt->fetch();
        return $token ?: null;
    }

    public function revokeToken(string $selector): bool
    {
        $stmt = $this->db->prepare('UPDATE auth_tokens SET revoked_at = NOW() WHERE selector = ?');
        return $stmt->execute([$selector]);
    }

    public function revokeUserTokens(int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE auth_tokens SET revoked_at = NOW() WHERE user_id = ?');
        return $stmt->execute([$userId]);
    }
}
