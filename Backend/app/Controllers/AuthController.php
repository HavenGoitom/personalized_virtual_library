<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function loginForm(): void
    {
        if (Auth::user()) {
            $this->redirect(BASE_PATH . '/books');
        }

        $this->render('auth/login', [
            'title' => 'Login',
            'active' => 'login'
        ]);
    }

    public function signupForm(): void
    {
        if (Auth::user()) {
            $this->redirect(BASE_PATH . '/books');
        }

        $this->render('auth/signup', [
            'title' => 'Signup',
            'active' => 'signup'
        ]);
    }

    public function register(): void
    {
        if (!$this->csrf()) {
            $this->flash('Invalid form submission.', 'error');
            $this->redirect(BASE_PATH . '/signup');
        }

        $displayName = trim($_POST['display_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        $errors = [];

        if (!Validator::required($displayName) || !Validator::maxLength($displayName, 100)) {
            $errors[] = 'Display name is required and must be under 100 characters.';
        }

        if (!Validator::username($username)) {
            $errors[] = 'Username must be 3–20 characters, start with a letter, and contain only letters, numbers, or underscores.';
        }

        if (!Validator::email($email)) {
            $errors[] = 'A valid email address is required.';
        }

        if (!Validator::strongPassword($password)) {
            $errors[] = 'Password must be at least 8 characters and include uppercase, lowercase, a digit, and a special character.';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match.';
        }

        $userModel = new User();

        if ($userModel->findByUsername($username)) {
            $errors[] = 'That username is already taken.';
        }

        if ($userModel->findByEmail($email)) {
            $errors[] = 'That email is already registered.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash($error, 'error');
            }

            $this->redirect(BASE_PATH . '/signup');
        }

        $created = $userModel->create([
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'display_name' => $displayName
        ]);

        if (!$created) {
            $this->flash('Unable to create account. Please try again.', 'error');
            $this->redirect(BASE_PATH . '/signup');
        }

        $user = $userModel->findByUsername($username);
        Auth::login($user, isset($_POST['remember_me']));
        $this->flash('Account created successfully.', 'success');
        $this->redirect(BASE_PATH . '/books');
    }

    public function login(): void
    {
        if (!$this->csrf()) {
            $this->flash('Invalid form submission.', 'error');
            $this->redirect(BASE_PATH . '/login');
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = [];

        if (!Validator::username($username)) {
            $errors[] = 'Invalid username or password.';
        }

        if (!Validator::password($password)) {
            $errors[] = 'Invalid username or password.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash($error, 'error');
            }
            $this->redirect(BASE_PATH . '/login');
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $this->flash('Invalid username or password.', 'error');
            $this->redirect(BASE_PATH . '/login');
        }

        Auth::login($user, isset($_POST['remember_me']));
        $this->flash('Signed in successfully.', 'success');
        $this->redirect(BASE_PATH . '/books');
    }

    public function logout(): void
    {
        Auth::logout();
        $this->flash('You have been logged out.', 'success');
        $this->redirect(BASE_PATH . '/login');
    }
}
