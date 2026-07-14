<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function all()
    {
        return User::all();
    }
    public function find($id)
    {
        return User::find($id);
    }
    public function create($data)
    {
        $user = new User($data);
        return $user->save();
    }
    public function update($id, $data)
    {
        $user = $this->find($id);

        if (!$user) {
            return false;
        }

        foreach ($data as $key => $value) {
            $user->$key = $value;
        }

        return $user->save();
    }

    public function delete($id)
    {
        $user = $this->find($id);

        if (!$user) {
            return false;
        }

        return $user->delete();
    }
}