<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Shelf;
use App\Models\Book;

class ShelfController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(BASE_PATH . '/login');
        }

        $items = (new Shelf())->getUserShelf($user['id']);

        $this->render('shelf/index', [
            'title' => 'My Shelf',
            'active' => 'shelf',
            'items' => $items,
            'user' => $user
        ]);
    }

    public function add(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(BASE_PATH . '/login');
        }

        if (!$this->csrf()) {
            $this->flash('Invalid form submission.', 'error');
            $this->redirect(BASE_PATH . '/books');
        }

        $bookId = (int)($_POST['book_id'] ?? 0);
        $book = (new Book())->findById($bookId);

        if (!$book) {
            $this->flash('Book not found.', 'error');
            $this->redirect(BASE_PATH . '/books');
        }

        try {
            (new Shelf())->addItem($user['id'], $bookId);
            $this->flash('Book added to your shelf.', 'success');
        } catch (\Exception $e) {
            $this->flash($e->getMessage(), 'error');
        }

        $this->redirect(BASE_PATH . '/books');
    }

    public function remove(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(BASE_PATH . '/login');
        }

        if (!$this->csrf()) {
            $this->flash('Invalid form submission.', 'error');
            $this->redirect(BASE_PATH . '/shelf');
        }

        $itemId = (int)($_POST['item_id'] ?? 0);
        $removed = (new Shelf())->removeItem($itemId, $user['id']);

        if ($removed) {
            $this->flash('Item removed from shelf.', 'success');
        } else {
            $this->flash('Shelf item not found.', 'error');
        }

        $this->redirect(BASE_PATH . '/shelf');
    }

    public function editForm(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(BASE_PATH . '/login');
        }

        $itemId = (int)($_GET['id'] ?? 0);
        $item = (new Shelf())->findById($itemId, $user['id']);

        if (!$item) {
            $this->flash('Shelf item not found.', 'error');
            $this->redirect(BASE_PATH . '/shelf');
        }

        $this->render('shelf/edit', [
            'title' => 'Edit Shelf Copy',
            'active' => 'shelf',
            'item' => $item
        ]);
    }

    public function update(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(BASE_PATH . '/login');
        }

        if (!$this->csrf()) {
            $this->flash('Invalid form submission.', 'error');
            $this->redirect(BASE_PATH . '/shelf');
        }

        $itemId = (int)($_POST['item_id'] ?? 0);
        $item = (new Shelf())->findById($itemId, $user['id']);

        if (!$item) {
            $this->flash('Shelf item not found.', 'error');
            $this->redirect(BASE_PATH . '/shelf');
        }

        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $normalizedCategory = strtolower(preg_replace('/[\s_-]+/', '', $category)) === 'fiction' ? 'Fiction' : 'Non-Fiction';

        $errors = [];

        if (!Validator::required($title)) {
            $errors[] = 'Title is required.';
        }

        if (!Validator::required($author)) {
            $errors[] = 'Author is required.';
        }

        if (!Validator::category($category)) {
            $errors[] = 'Category must be Fiction or Non-Fiction.';
        }

        $customCover = $item['custom_cover_image'];
        if (!empty($_FILES['cover']['name'])) {
            $uploadedPath = Upload::image($_FILES['cover']);
            if (!$uploadedPath) {
                $errors[] = 'Uploaded cover must be a valid image file under 2MB.';
            } else {
                $customCover = $uploadedPath;
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash($error, 'error');
            }
            $this->redirect(BASE_PATH . '/shelf/edit?id=' . $itemId);
        }

        $shelfModel = new Shelf();
        $shelfModel->updateItem($itemId, $user['id'], []);

        if ($item['book_owner_id'] === $user['id']) {
            (new Book())->update($item['book_id'], [
                'title' => $title,
                'author' => $author,
                'category' => $normalizedCategory,
                'description' => $description,
                'url' => $item['original_url'],
                'cover_image' => $customCover ?: $item['original_cover_image']
            ]);
        }

        $this->flash('Shelf copy updated.', 'success');
        $this->redirect(BASE_PATH . '/shelf');
    }
}
