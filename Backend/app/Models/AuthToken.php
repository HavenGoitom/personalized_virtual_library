<?php

namespace App\Models;

use App\Core\Model;

class AuthToken extends Model
{
    private ?bool $legacyMode = null;

    private function usesLegacyTokenField(): bool
    {
        if ($this->legacyMode !== null) {
            return $this->legacyMode;
        }

        $stmt = $this->db->prepare("SHOW COLUMNS FROM auth_tokens LIKE 'token'");
        $stmt->execute();
        $this->legacyMode = (bool)$stmt->fetch();
        return $this->legacyMode;
    }

    public function createToken(int $userId): string
    {
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        if ($this->usesLegacyTokenField()) {
            $token = bin2hex(random_bytes(32));
            $tokenHash = password_hash($token, PASSWORD_BCRYPT);
            $stmt = $this->db->prepare('INSERT INTO auth_tokens (user_id, token, expires_at) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $tokenHash, $expiresAt]);
            return $token;
        }

        $selector = bin2hex(random_bytes(12));
        $validator = bin2hex(random_bytes(32));
        $validatorHash = password_hash($validator, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare('INSERT INTO auth_tokens (user_id, selector, validator_hash, expires_at) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $selector, $validatorHash, $expiresAt]);

        return $selector . ':' . $validator;
    }

    public function findValidTokenBySelector(string $selector): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM auth_tokens WHERE selector = ? AND revoked_at IS NULL AND expires_at > NOW()');
        $stmt->execute([$selector]);
        $token = $stmt->fetch();
        return $token ?: null;
    }

    public function findValidTokenByToken(string $token): ?array
    {
        if (!$this->usesLegacyTokenField()) {
            return null;
        }

        $stmt = $this->db->prepare('SELECT * FROM auth_tokens WHERE revoked_at IS NULL AND expires_at > NOW()');
        $stmt->execute();
        foreach ($stmt->fetchAll() as $row) {
            if (password_verify($token, $row['token'])) {
                return $row;
            }
        }

        return null;
    }

    public function revokeTokenBySelector(string $selector): bool
    {
        $stmt = $this->db->prepare('UPDATE auth_tokens SET revoked_at = NOW() WHERE selector = ?');
        return $stmt->execute([$selector]);
    }

    public function revokeTokenByToken(string $token): bool
    {
        if (!$this->usesLegacyTokenField()) {
            return false;
        }

        $stmt = $this->db->prepare('SELECT id, token FROM auth_tokens WHERE revoked_at IS NULL AND expires_at > NOW()');
        $stmt->execute();
        foreach ($stmt->fetchAll() as $row) {
            if (password_verify($token, $row['token'])) {
                $delete = $this->db->prepare('UPDATE auth_tokens SET revoked_at = NOW() WHERE id = ?');
                return $delete->execute([$row['id']]);
            }
        }

        return false;
    }

    public function revokeUserTokens(int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE auth_tokens SET revoked_at = NOW() WHERE user_id = ?');
        return $stmt->execute([$userId]);
    }
}
