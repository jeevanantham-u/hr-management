<?php
namespace App\Models;

use App\Core\Database;
use App\Core\Model;
class User extends Model
{
    public function getAllUsers()
    {
        return $this->all();
    }

    public function findById($id)
    {
        return $this->find([$id]);
    }
    public function createUser($data)
    {
        return $this->insert($data);
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
        return $this->update($setString, $values);
    }

    public function deleteById($id)
    {
        return $this->delete([$id]);
    }
}