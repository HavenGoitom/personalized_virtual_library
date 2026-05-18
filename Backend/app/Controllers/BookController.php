<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\Book;

class BookController extends Controller
{
    public function index(): void
    {
        $query = trim($_GET['search'] ?? '');
        $books = (new Book())->all($query);

        $this->render('books/index', [
            'title' => 'Books',
            'active' => 'books',
            'books' => $books,
            'search' => $query,
            'user' => Auth::user()
        ]);
    }

    public function createForm(): void
    {
        if (!Auth::user()) {
            $this->flash('Sign in to add books.', 'error');
            $this->redirect(BASE_PATH . '/login');
        }

        $this->render('books/create', [
            'title' => 'Add Book',
            'active' => 'books'
        ]);
    }

    public function create(): void
    {
        if (!Auth::user()) {
            $this->redirect(BASE_PATH . '/login');
        }

        if (!$this->csrf()) {
            $this->flash('Invalid form submission.', 'error');
            $this->redirect(BASE_PATH . '/books/create');
        }

        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $normalizedCategory = strtolower(preg_replace('/[\s_-]+/', '', $category)) === 'fiction' ? 'Fiction' : 'Non-Fiction';

        $errors = [];

        if (!Validator::required($title)) {
            $errors[] = 'Book title is required.';
        }

        if (!Validator::required($author)) {
            $errors[] = 'Book author is required.';
        }

        if (!Validator::category($category)) {
            $errors[] = 'Category must be Fiction or Non-Fiction.';
        }

        if (!Validator::url($url)) {
            $errors[] = 'A valid book URL is required.';
        }

        $coverPath = Upload::image($_FILES['cover'] ?? []);
        if (!$coverPath) {
            $errors[] = 'A cover image is required and must be a valid image file under 2MB.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash($error, 'error');
            }
            $this->redirect(BASE_PATH . '/books/create');
        }

        $bookModel = new Book();
        $saved = $bookModel->create([
            'title' => $title,
            'author' => $author,
            'category' => $normalizedCategory,
            'description' => $description,
            'url' => $url,
            'cover_image' => $coverPath,
            'owner_id' => Auth::user()['id']
        ]);

        if (!$saved) {
            $this->flash('Unable to save the book. Please try again.', 'error');
            $this->redirect(BASE_PATH . '/books/create');
        }

        $this->flash('Book added successfully.', 'success');
        $this->redirect(BASE_PATH . '/books');
    }

    public function editForm(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(BASE_PATH . '/login');
        }

        $id = (int)($_GET['id'] ?? 0);
        $book = (new Book())->findById($id);

        if (!$book || $book['owner_id'] !== $user['id']) {
            $this->flash('Book not found or access denied.', 'error');
            $this->redirect(BASE_PATH . '/books');
        }

        $this->render('books/edit', [
            'title' => 'Edit Book',
            'active' => 'books',
            'book' => $book
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
            $this->redirect(BASE_PATH . '/books');
        }

        $id = (int)($_POST['id'] ?? 0);
        $bookModel = new Book();
        $book = $bookModel->findById($id);

        if (!$book || $book['owner_id'] !== $user['id']) {
            $this->flash('Book not found or access denied.', 'error');
            $this->redirect(BASE_PATH . '/books');
        }

        $title = trim($_POST['title'] ?? '');
        $author = trim($_POST['author'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $url = trim($_POST['url'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $normalizedCategory = strtolower(preg_replace('/[\s_-]+/', '', $category)) === 'fiction' ? 'Fiction' : 'Non-Fiction';

        $errors = [];

        if (!Validator::required($title)) {
            $errors[] = 'Book title is required.';
        }

        if (!Validator::required($author)) {
            $errors[] = 'Book author is required.';
        }

        if (!Validator::category($category)) {
            $errors[] = 'Category must be Fiction or Non-Fiction.';
        }

        if (!Validator::url($url)) {
            $errors[] = 'A valid book URL is required.';
        }

        $coverPath = $book['cover_image'];
        if (!empty($_FILES['cover']['name'])) {
            $uploadedPath = Upload::image($_FILES['cover']);
            if (!$uploadedPath) {
                $errors[] = 'Uploaded cover must be a valid image file under 2MB.';
            } else {
                $coverPath = $uploadedPath;
            }
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash($error, 'error');
            }
            $this->redirect(BASE_PATH . '/books/edit?id=' . $id);
        }

        $updated = $bookModel->update($id, [
            'title' => $title,
            'author' => $author,
            'category' => $normalizedCategory,
            'description' => $description,
            'url' => $url,
            'cover_image' => $coverPath
        ]);

        if (!$updated) {
            $this->flash('Unable to update the book.', 'error');
            $this->redirect(BASE_PATH . '/books/edit?id=' . $id);
        }

        $this->flash('Book updated successfully.', 'success');
        $this->redirect(BASE_PATH . '/books');
    }

    public function delete(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(BASE_PATH . '/login');
        }

        if (!$this->csrf()) {
            $this->flash('Invalid form submission.', 'error');
            $this->redirect(BASE_PATH . '/books');
        }

        $id = (int)($_POST['id'] ?? 0);
        $book = (new Book())->findById($id);

        if (!$book || $book['owner_id'] !== $user['id']) {
            $this->flash('Book not found or access denied.', 'error');
            $this->redirect(BASE_PATH . '/books');
        }

        (new Book())->delete($id);
        $this->flash('Book deleted successfully.', 'success');
        $this->redirect(BASE_PATH . '/books');
    }
}
