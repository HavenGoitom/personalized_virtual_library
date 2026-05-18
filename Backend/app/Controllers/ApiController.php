<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Validator;
use App\Models\Book;
use App\Models\Shelf;
use App\Models\User;

class ApiController extends Controller
{
    public function register(): void
    {
        $data = Request::all();

        $displayName = trim($data['display_name'] ?? '');
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = $data['password'] ?? '';
        $passwordConfirm = $data['password_confirm'] ?? '';

        $errors = [];

        if (!Validator::required($displayName) || !Validator::maxLength($displayName, 100)) {
            $errors['display_name'] = 'Display name is required and must be under 100 characters.';
        }

        if (!Validator::username($username)) {
            $errors['username'] = 'Username must be 3–20 characters, start with a letter, and contain only letters, numbers, or underscores.';
        }

        if (!Validator::email($email)) {
            $errors['email'] = 'A valid email is required.';
        }

        if (!Validator::password($password)) {
            $errors['password'] = 'Password must be at least 8 characters.';
        }

        if ($password !== $passwordConfirm) {
            $errors['password_confirm'] = 'Passwords must match.';
        }

        $userModel = new User();

        if ($userModel->findByUsername($username)) {
            $errors['username'] = 'That username is already taken.';
        }

        if ($userModel->findByEmail($email)) {
            $errors['email'] = 'That email is already registered.';
        }

        if (!empty($errors)) {
            Response::json(false, 'Validation failed', ['errors' => $errors], 422);
        }

        $created = $userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'display_name' => $displayName
        ]);

        if (!$created) {
            Response::json(false, 'Unable to create account.', null, 500);
        }

        $user = $userModel->findByUsername($username);
        Auth::login($user, isset($data['remember_me']));

        Response::json(true, 'Account created successfully.', ['user' => $user], 201);
    }

    public function login(): void
    {
        $data = Request::all();
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if (!Validator::username($username) || !Validator::password($password)) {
            Response::json(false, 'Invalid username or password.', null, 401);
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            Response::json(false, 'Invalid username or password.', null, 401);
        }

        Auth::login($user, isset($data['remember_me']));
        Response::json(true, 'Signed in successfully.', ['user' => $user], 200);
    }

    public function logout(): void
    {
        if (!Auth::user()) {
            Response::json(false, 'Not signed in.', null, 401);
        }

        Auth::logout();
        Response::json(true, 'Logged out successfully.');
    }

    public function books(): void
    {
        $query = trim($_GET['q'] ?? '');
        $books = (new Book())->all($query);

        Response::json(true, 'Books fetched successfully.', ['books' => $books]);
    }

    public function booksCreate(): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(false, 'Unauthorized.', null, 401);
        }

        if ($_SERVER['CONTENT_TYPE'] ?? '' === 'application/json') {
            $data = Request::all();
        } else {
            $data = $_POST;
        }

        $title = trim($data['title'] ?? '');
        $author = trim($data['author'] ?? '');
        $category = trim($data['category'] ?? '');
        $url = trim($data['url'] ?? '');
        $description = trim($data['description'] ?? '');

        $errors = [];

        if (!Validator::required($title)) {
            $errors['title'] = 'Title is required.';
        }

        if (!Validator::required($author)) {
            $errors['author'] = 'Author is required.';
        }

        if (!Validator::category($category)) {
            $errors['category'] = 'Category must be Fiction or Non-Fiction.';
        }

        if (!Validator::url($url)) {
            $errors['url'] = 'A valid URL is required.';
        }

        if (!empty($errors)) {
            Response::json(false, 'Validation failed.', ['errors' => $errors], 422);
        }

        $created = (new Book())->create([
            'title' => $title,
            'author' => $author,
            'category' => $category,
            'description' => $description,
            'url' => $url,
            'cover_image' => $data['cover_image'] ?? '',
            'owner_id' => $user['id']
        ]);

        if (!$created) {
            Response::json(false, 'Unable to create book.', null, 500);
        }

        Response::json(true, 'Book added successfully.', null, 201);
    }

    public function shelf(): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(false, 'Unauthorized.', null, 401);
        }

        $items = (new Shelf())->getUserShelf($user['id']);
        Response::json(true, 'Shelf fetched successfully.', ['items' => $items]);
    }

    public function shelfAdd(): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(false, 'Unauthorized.', null, 401);
        }

        $data = Request::all();
        $bookId = (int)($data['book_id'] ?? 0);

        if (!$bookId) {
            Response::json(false, 'Book ID is required.', null, 422);
        }

        try {
            $item = (new Shelf())->addItem($user['id'], $bookId);
        } catch (\Exception $e) {
            Response::json(false, $e->getMessage(), null, 400);
        }

        Response::json(true, 'Added to shelf.', ['item' => $item], 201);
    }

    public function shelfRemove(): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(false, 'Unauthorized.', null, 401);
        }

        $data = Request::all();
        $itemId = (int)($data['item_id'] ?? 0);

        if (!$itemId) {
            Response::json(false, 'Shelf item ID is required.', null, 422);
        }

        $removed = (new Shelf())->removeItem($itemId, $user['id']);
        if (!$removed) {
            Response::json(false, 'Unable to remove item.', null, 404);
        }

        Response::json(true, 'Removed from shelf.');
    }

    public function profileUpdate(): void
    {
        $user = Auth::user();
        if (!$user) {
            Response::json(false, 'Unauthorized.', null, 401);
        }

        $data = Request::all();
        $displayName = trim($data['display_name'] ?? '');
        $bio = trim($data['bio'] ?? '');

        $errors = [];

        if (!Validator::required($displayName) || !Validator::maxLength($displayName, 100)) {
            $errors['display_name'] = 'Display name is required and must be under 100 characters.';
        }

        if (!empty($errors)) {
            Response::json(false, 'Validation failed.', ['errors' => $errors], 422);
        }

        (new User())->updateProfile($user['id'], [
            'display_name' => $displayName,
            'bio' => $bio
        ]);

        Response::json(true, 'Profile updated successfully.');
    }
}
