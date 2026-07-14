<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    public function all(): array
    {
        return Database::select("SELECT id, email, role_id FROM users WHERE is_active = 1");
    }

    public function find($id): array
    {
        return Database::selectOne("SELECT id, email, username  FROM users WHERE id = :id AND is_active = 1", $id);
    }

    public function insert($data): int
    {
        return Database::insert("INSERT INTO users (username, email, password, role_id) VALUES (:username, :email, :password, :role_id)", $data);
    }

    public function update($setValue, $id): int
    {
        return Database::update("UPDATE users SET {$setValue} WHERE id = :id", $id);
    }

    public function delete($id): int
    {
        return Database::delete("UPDATE users SET is_active = 0 WHERE id = :id", $id);
    }
}