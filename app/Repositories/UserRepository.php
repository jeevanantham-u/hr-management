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

    public function findByEmail($email)
    {
        return User::findByEmail($email);
    }

    public function create($data)
    {
        $user = new User($data);
        if (isset($data['password'])) {
            $user->setPasswordAttribute($data['password']);
        }
        $user->save();
        return $user;
    }

    public function update($id, $data)
    {
        $user = $this->find($id);

        if (!$user) {
            return false;
        }

        foreach ($data as $k => $v) {
            if ($k === 'password') {
                $user->setPasswordAttribute($v);
            } else {
                $user->$k = $v;
            }
        }

        return $user->save();
    }

    public function delete($id)
    {
        $user = $this->find($id);

        if (!$user || $user->is_active == 0) {
            return false;
        }

        $user->is_active = 0;

        return $user->save();
    }
}