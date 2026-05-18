<?php

namespace App\Models;

use App\Core\Model;

class Book extends Model
{
    public function all(string $search = ''): array
    {
        if ($search === '') {
            $stmt = $this->db->query(
                'SELECT books.*, users.username FROM books JOIN users ON users.id = books.owner_id ORDER BY books.created_at DESC'
            );
            return $stmt->fetchAll();
        }

        $query = '%' . $search . '%';
        $stmt = $this->db->prepare(
            'SELECT books.*, users.username FROM books JOIN users ON users.id = books.owner_id WHERE books.title LIKE ? OR books.author LIKE ? ORDER BY books.created_at DESC'
        );
        $stmt->execute([$query, $query]);
        return $stmt->fetchAll();
    }

    public function findById(int $id)
    {
        $stmt = $this->db->prepare('SELECT books.*, users.username FROM books JOIN users ON users.id = books.owner_id WHERE books.id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            'INSERT INTO books (title, author, category, description, url, cover_image, owner_id) VALUES (:title, :author, :category, :description, :url, :cover_image, :owner_id)'
        );

        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE books SET title = :title, author = :author, category = :category, description = :description, url = :url, cover_image = :cover_image WHERE id = :id'
        );

        $data['id'] = $id;
        return $stmt->execute($data);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM books WHERE id = ?');
        return $stmt->execute([$id]);
    }
}
