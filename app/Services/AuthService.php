<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Models\User;
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

        return [
            'user' => $user->toArray(),
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

}