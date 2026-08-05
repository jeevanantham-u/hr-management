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
        // return 0;
    }

    public function create($data)
    {
        return User::create($data);
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

        if (!$user) {
            return false;
        }
        return $user->delete();
    }
}