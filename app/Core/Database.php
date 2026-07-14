<?php
namespace App\Core;

use PDO;
use PDOException;


class Database
{
    private static ?PDO $connection = null;

    public static function connect(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']};charset=utf8mb4";
                $username = $_ENV['DB_USER'];
                $password = $_ENV['DB_PASSWORD'];

                self::$connection = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } catch (PDOException $e) {
                throw new \Exception("Database Connection Failed: " . $e->getMessage());
            }
        }

        return self::$connection;
    }

    public static function getInstance(): PDO
    {
        if (self::$connection == null) {
            throw new \Exception("Database not connected. Call Database::connect() first.");
        }

        return self::$connection;
    }

    public static function query(string $sql, array $bindings = []): \PDOStatement
    {
        $connection = self::getInstance();
        $statement = $connection->prepare($sql);
        $statement->execute($bindings);
        return $statement;
    }

    public static function select(string $sql, array $bindings = []): array
    {
        return self::query($sql, $bindings)->fetchAll();
    }

    public static function selectOne(string $sql, array $bindings = []): ?array
    {
        $row = self::query($sql, $bindings)->fetch();
        return $row ?: null; 
    }

    public static function insert(string $sql, array $bindings = []): int
    {
        self::query($sql, $bindings);
        return (int) self::getInstance()->lastInsertId();
    }

    public static function update(string $sql, array $bindings = []): int
    {
        return self::query($sql, $bindings)->rowCount();
    }

    public static function delete(string $sql, array $bindings = []): int
    {
        return self::query($sql, $bindings)->rowCount();
    }

    public static function transaction(callable $callback)
    {
        $connection = self::getInstance();

        try {
            $connection->beginTransaction();
            $result = $callback($connection);
            $connection->commit();
            return $result;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }

    public static function close()
    {
        return self::$connection = null;
    }
}