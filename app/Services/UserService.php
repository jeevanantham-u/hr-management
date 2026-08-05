<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    private UserRepository $repository;

    public function __construct()
    {
        $this->repository = new UserRepository();
    }

    public function getAllUsers()
    {
        return $this->repository->all();
    }

    public function getOneUser($id)
    {
        return $this->repository->find($id);
    }

    public function createUser($data)
    {
        return $this->repository->create($data);
    }

    public function editUser($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    public function deleteUser($id)
    {
        return $this->repository->delete($id);
    }
}