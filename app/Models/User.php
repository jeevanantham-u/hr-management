<?php
namespace App\Models;

use App\Core\Model;
class User extends Model
{
    public function getAllUsers()
    {
       ret
    }

    public function findById($id)
    {
        $stmt = $this->db->prepare("SELECT id, email, username  FROM users WHERE id = :id AND is_active = 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }

    public function createUser($data)
    {
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, role_id) VALUES (:username, :email, :password, :role_id)");
        $stmt->execute([
            'username' => $data['username'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role_id' => $data['role_id']
        ]);

        $id = $this->db->lastInsertId();

        return $this->findById($id);
    }

    public function editUserById($id, $data)
    {
        if (empty($data)) {
            return false;
        }

        $fields = [];
        foreach ($data as $key => $value) {
            $fields[] = "{$key} = :{$key}";
        }

        $setString = implode(', ', $fields);
        $stmt = $this->db->prepare("UPDATE users SET {$setString} WHERE id = :id");
        $data['id'] = $id;
        $stmt->execute($data);

        return $this->findById($id);
    }

    public function deleteById($id)
    {
        $stmt = $this->db->prepare("UPDATE users SET is_active = 0 WHERE id = :id");
        if ($stmt->execute(['id' => $id]) == true) {
            return $id;
        }
    }
}