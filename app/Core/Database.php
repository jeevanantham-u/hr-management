<?php
namespace App\Core;

use PDO;
use PDOException;


class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            try {
                // Change these credentials to match your setup
                $dsn = "mysql:host=localhost;dbname=hr_management;charset=utf8mb4";
                $username = "root";
                $password = "";
                
                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                // This will be caught automatically by your core\ExceptionHandler!
                throw new \Exception("Database Connection Failed: " . $e->getMessage());
            }
        }

        return self::$instance;
    }
}