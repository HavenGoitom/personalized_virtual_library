<?php

namespace App\Core;

abstract class Controller
{
    protected function render(string $view, array $params = []): void
    {
        extract($params, EXTR_SKIP);
        require __DIR__ . '/../Views/partials/header.php';
        require __DIR__ . '/../Views/' . $view . '.php';
        require __DIR__ . '/../Views/partials/footer.php';
    }

    protected function redirect(string $path): void
    {
        header('Location: ' . $path);
        exit;
    }

    protected function json(bool $success, string $message, $data = null, int $status = 200): void
    {
        Response::json($success, $message, $data, $status);
    }

    protected function csrf(): bool
    {
        return Csrf::verify($_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    }

    protected function flash(string $text, string $type = 'success'): void
    {
        Session::flash('message', ['text' => $text, 'type' => $type]);
    }
}
