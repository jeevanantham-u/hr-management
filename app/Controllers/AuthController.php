<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    public function login(Request $request)
    {
        $data = [
            'email' => $request->body('email'),
            'password' => $request->body('password')
        ];

        $this->validate($data, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $result = $this->authService->login($data['email'], $data['password']);

        $this->success($result, 'User login successfuly', 200);
    }

    public function register(Request $request)
    {
        $data = [
            'username' => $request->body('username'),
            'email' => $request->body('email'),
            'password' => $request->body('password'),
            'role_id' => $request->body('role_id')
        ];

        $this->validate($data, [
            'username' => 'required',
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = $this->authService->register($data);

        $this->success($user, 'Registration successful', 201);
    }
}
