<?php
namespace App\Models;

class User extends BaseModel {
    
    public function getAllUsers() {
        // Look how clean this is! $this->db is already available.
        $stmt = $this->db->query("SELECT id, email, role_id FROM users");
        return $stmt->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, email, username  FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch();
    }
}