<?php

namespace App\Models;

use App\Core\Model;
use PDO;

class User extends Model
{
    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash, display_name) VALUES (:username, :email, :password_hash, :display_name)'
        );

        return $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_BCRYPT),
            'display_name' => $data['display_name']
        ]);
    }

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function updateProfile(int $userId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE users SET display_name = :display_name, bio = :bio WHERE id = :id'
        );

        return $stmt->execute([
            'display_name' => $data['display_name'],
            'bio' => $data['bio'] ?? null,
            'id' => $userId
        ]);
    }
}
