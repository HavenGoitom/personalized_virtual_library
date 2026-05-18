<?php

namespace App\Core;

class Response
{
    public static function json(
        bool $success,
        string $message = '',
        $data = null,
        int $status = 200
    ) {
        http_response_code($status);

        header('Content-Type: application/json');

        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);

        exit;
    }
}
