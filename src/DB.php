<?php

declare(strict_types=1);

namespace QueryLite;

use QueryLite\Core\Connection;
use QueryLite\Core\QueryBuilder;

/**
 * Static facade for quick usage.
 *
 * Example:
 *  DB::connect([...]);
 *  DB::table('users')->where('status', 'active')->get();
 */
class DB
{
    private static ?Connection $connection = null;

    /**
     * Initialise the connection once.
     *
     * @param array{
     *     host: string,
     *     database: string,
     *     username: string,
     *     password: string,
     *     charset?: string
     * } $config
     */
    public static function connect(array $config): void
    {
        self::$connection = new Connection($config);
    }

    public static function connection(): Connection
    {
        if (self::$connection === null) {
            throw new \RuntimeException('Database connection has not been initialised. Call DB::connect() first.');
        }

        return self::$connection;
    }

    public static function table(string $table): QueryBuilder
    {
        return new QueryBuilder(self::connection(), $table);
    }
}
