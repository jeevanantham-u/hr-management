<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\User;
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;
use Exception;

class AuthService
{
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function login($email, $password)
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            throw new Exception('Invalid email or password');
        }

        if (!$user->is_active) {
            throw new Exception('User account is inactive');
        }

        date_default_timezone_set("Asia/Kolkata");

        $token = $this->generateToken($user);
        $this->userRepository->update($user->id, ['last_login' => date('Y-m-d H:i:s')]);

        return [
            'user' => $user->toArray(),
            'token' => $token
        ];
    }

    public function register($data)
    {
        $existing = $this->userRepository->findByEmail($data['email']);
        if ($existing) {
            throw new Exception('Email already exists');
        }
        $user = $this->userRepository->create($data);
        return $user;
    }

    public function generateToken(User $user)
    {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? getenv('JWT_SECRET');

        if (!$jwtSecret) {
            throw new Exception('JWT secret is not configured');
        }

        $payload = [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role_id,
            'iat' => time(),
            'iss' => 'localhost/resources',
            'exp' => time() + (3600)
        ];

        return JWT::encode($payload, $jwtSecret, 'HS256');
    }

    public function verifyToken($token)
    {
        $jwtSecret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? getenv('JWT_SECRET');

        if (!$jwtSecret) {
            throw new Exception('JWT secret is not configured');
        }

        try {
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
            return $decoded;
        } catch (Exception $e) {
            throw new Exception('Invalid or expired token: ' . $e->getMessage());
        }
    }

}