<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\Core\Request;
use Exception;

class AuthMiddleware
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function handle(Request $request)
    {
        $token = $this->extractToken($request);

        if (!$token) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized - No token provided'
            ]);
            exit;
        }

        try {
            $user = $this->authService->verifyToken($token);
            // Store authenticated user in request
            $request->setUser($user);
            return true;
        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized - ' . $e->getMessage()
            ]);
            exit;
        }
    }

    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization') ?? $request->header('authorization');

        if (!$authHeader) {
            return null;
        }

        if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        return null;
    }
}