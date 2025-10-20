<?php

namespace Bogi\EvoteSimple\Core;

use PDO;
use PDOException;

/**
 * Database Connection Manager
 * Handles secure database connections for the eVote system
 */
class Database
{
    private static ?PDO $connection = null;

    private function __construct() {}

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            try {
                $host = $_ENV['DB_HOST'] ?? 'mysql';
                $port = $_ENV['DB_PORT'] ?? '3306';
                $dbname = $_ENV['DB_DATABASE'] ?? 'evote_simple';
                $username = $_ENV['DB_USERNAME'] ?? 'sail';
                $password = $_ENV['DB_PASSWORD'] ?? 'password';

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_PERSISTENT => false,
                ];

                self::$connection = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                throw new \RuntimeException("Database connection failed: " . $e->getMessage());
            }
        }

        return self::$connection;
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollback(): bool
    {
        return self::getConnection()->rollback();
    }
}
