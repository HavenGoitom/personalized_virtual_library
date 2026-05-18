<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Upload;
use App\Core\Validator;
use App\Models\User;

class ProfileController extends Controller
{
    public function index(): void
    {
        $user = Auth::user();
        if (!$user) {
            $this->redirect(BASE_PATH . '/login');
        }

        $this->render('profile/index', [
            'title' => 'Profile',
            'active' => 'profile',
            'user' => $user
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
            $this->redirect(BASE_PATH . '/profile');
        }

        $displayName = trim($_POST['display_name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        $errors = [];

        if (!Validator::required($displayName) || !Validator::maxLength($displayName, 100)) {
            $errors[] = 'Display name is required and must be under 100 characters.';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->flash($error, 'error');
            }
            $this->redirect(BASE_PATH . '/profile');
        }

        (new User())->updateProfile($user['id'], [
            'display_name' => $displayName,
            'bio' => $bio
        ]);

        $this->flash('Profile saved successfully.', 'success');
        $this->redirect(BASE_PATH . '/profile');
    }
}
