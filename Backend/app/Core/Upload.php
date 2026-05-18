<?php

namespace App\Core;

class Upload
{
    public static function image(array $file, int $maxBytes = 2097152): ?string
    {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > $maxBytes) {
            return null;
        }

        $mime = mime_content_type($file['tmp_name']);
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            return null;
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin'
        };

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $config = require __DIR__ . '/../../config/app.php';
        $destination = rtrim($config['upload_dir'], '/\\') . DIRECTORY_SEPARATOR . $filename;

        if (!is_dir(dirname($destination))) {
            mkdir(dirname($destination), 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return '/uploads/' . $filename;
        }

        return null;
    }
}
