<?php

namespace App\Core;

use Throwable;

class ExceptionHandler
{
    public static function register(): void
    {
        // echo " hello exception handler ";
        set_exception_handler(function (Throwable $e): void {
            if (ob_get_length()) {
                ob_clean();
            }

            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
            ]);
        });
    }
}

