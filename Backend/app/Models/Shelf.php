<?php

namespace App\Models;

use App\Core\Model;

class Shelf extends Model
{
    public function getUserShelf(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT shelf_items.*, books.title AS original_title, books.author AS original_author, books.category AS original_category, books.description AS original_description, books.cover_image AS original_cover_image, books.url AS original_url, books.owner_id AS book_owner_id, users.username AS book_owner_username FROM shelf_items JOIN books ON books.id = shelf_items.book_id JOIN users ON users.id = books.owner_id WHERE shelf_items.user_id = ? ORDER BY shelf_items.created_at DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findById(int $itemId, int $userId): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT shelf_items.*, books.title AS original_title, books.author AS original_author, books.category AS original_category, books.description AS original_description, books.cover_image AS original_cover_image, books.url AS original_url, books.owner_id AS book_owner_id, users.username AS book_owner_username FROM shelf_items JOIN books ON books.id = shelf_items.book_id JOIN users ON users.id = books.owner_id WHERE shelf_items.id = ? AND shelf_items.user_id = ?'
        );
        $stmt->execute([$itemId, $userId]);
        $item = $stmt->fetch();
        return $item ?: null;
    }

    public function exists(int $userId, int $bookId): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM shelf_items WHERE user_id = ? AND book_id = ?');
        $stmt->execute([$userId, $bookId]);
        return (bool)$stmt->fetch();
    }

    public function addItem(int $userId, int $bookId): array
    {
        if ($this->exists($userId, $bookId)) {
            throw new \Exception('This book is already on your shelf.');
        }

        $stmt = $this->db->prepare('INSERT INTO shelf_items (user_id, book_id) VALUES (?, ?)');
        $stmt->execute([$userId, $bookId]);

        return $this->findById((int)$this->db->lastInsertId(), $userId);
    }

    public function removeItem(int $itemId, int $userId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM shelf_items WHERE id = ? AND user_id = ?');
        $stmt->execute([$itemId, $userId]);
        return $stmt->rowCount() > 0;
    }

    public function updateItem(int $itemId, int $userId, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE shelf_items SET updated_at = NOW() WHERE id = :id AND user_id = :user_id'
        );

        return $stmt->execute([
            'id' => $itemId,
            'user_id' => $userId
        ]);
    }
}
