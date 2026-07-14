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

    public function findById($id)
    {
        return $this->repository->find([$id]);
    }
    public function createUser($data)
    {
        return $this->repository->insert($data);
    }

    public function editUserById($id, $data)
    {
        if (empty($data)) {
            return false;
        }

        $fields = [];
        $values = ['id' => $id];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
            $values[$key] = $value;
        }

        $setString = implode(', ', $fields);
        return $this->repository->update($setString, $values);
    }

    public function deleteById($id)
    {
        return $this->repository->delete([$id]);
    }
}