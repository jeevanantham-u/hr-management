<?php
namespace App\Models;

use App\Core\Database;
use PDO;

abstract class BaseModel {
    protected PDO $db;

    public function __construct() {
        // Automatically assign the single PDO instance to the model
        $this->db = Database::connect();
    }
}