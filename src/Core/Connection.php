<?php

declare(strict_types=1);

namespace QueryLite\Core;

use PDO;
use PDOException;

/**
 * Manages the PDO database connection.
 */
class Connection
{
    private PDO $pdo;

    /**
     * @param array{
     *     host: string,
     *     database: string,
     *     username: string,
     *     password: string,
     *     charset?: string
     * } $config
     */
    public function __construct(array $config)
    {
        $host     = $config['host'] ?? '127.0.0.1';
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? '';
        $password = $config['password'] ?? '';
        $charset  = $config['charset'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$database};charset={$charset}";

        try {
            $this->pdo = new PDO(
                $dsn,
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            throw new PDOException('Database connection failed: ' . $e->getMessage(), (int) $e->getCode());
        }
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
