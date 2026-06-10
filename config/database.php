<?php

class Database
{
    private $host = 'localhost';
    private $db_name = 'hr_management';
    private $username = 'root';
    private $password = '';
    private $conn;
    public function getConnection(): ?PDO
    {
        try {
            $this->conn = null;
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
            exit;
        }

        return $this->conn;
    }
}