<?php
namespace App\Core;

use App\Core\Validator;

class Controller
{
    public function success($data, string $message, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }

    public function error(string $message, $errors, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        echo json_encode($response);
        exit;
    }

    protected function validate(array $data, array $rules): array
    {
        $validator = new Validator($rules);
        return $validator->validate($data);
    }
}