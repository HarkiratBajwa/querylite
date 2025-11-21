<?php

declare(strict_types=1);

namespace QueryLite\Core;

use PDO;

/**
 * Very small query builder / mini ORM.
 *
 * Example:
 *  DB::table('users')
 *      ->where('status', 'active')
 *      ->orderBy('created_at', 'desc')
 *      ->limit(10)
 *      ->get();
 */
class QueryBuilder
{
    private Connection $connection;
    private string $table;

    /** @var string[] */
    private array $columns = ['*'];

    /** @var array<int, array<string, mixed>> */
    private array $wheres = [];

    /** @var array<string, mixed> */
    private array $bindings = [];

    /** @var array<int, array{column: string, direction: string}> */
    private array $orders = [];

    private ?int $limit = null;
    private ?int $offset = null;

    public function __construct(Connection $connection, string $table)
    {
        $this->connection = $connection;
        $this->table      = $table;
    }

    /**
     * Choose selected columns.
     */
    public function select(string ...$columns): self
    {
        if ($columns !== []) {
            $this->columns = $columns;
        }

        return $this;
    }

    /**
     * Basic WHERE: where('status', 'active')
     * or where('age', '>', 18)
     */
    public function where(string $column, mixed $operatorOrValue, mixed $value = null, string $boolean = 'AND'): self
    {
        // where('status', 'active')
        if ($value === null) {
            $operator = '=';
            $value    = $operatorOrValue;
        } else {
            // where('age', '>', 18)
            $operator = $operatorOrValue;
        }

        $placeholder = $this->makePlaceholder('w');
        $this->wheres[] = [
            'boolean'     => $boolean,
            'column'      => $column,
            'operator'    => $operator,
            'placeholder' => $placeholder,
        ];

        $this->bindings[$placeholder] = $value;

        return $this;
    }

    /**
     * OR WHERE
     */
    public function orWhere(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        return $this->where($column, $operatorOrValue, $value, 'OR');
    }

    /**
     * WHERE IN: whereIn('id', [1, 2, 3])
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        $placeholders = [];

        foreach ($values as $value) {
            $placeholder = $this->makePlaceholder('in');
            $placeholders[] = ':' . $placeholder;
            $this->bindings[$placeholder] = $value;
        }

        $this->wheres[] = [
            'boolean'      => $boolean,
            'type'         => 'IN',
            'column'       => $column,
            'placeholders' => $placeholders,
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';

        $this->orders[] = [
            'column'    => $column,
            'direction' => $direction,
        ];

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Get all rows as an array.
     *
     * @return array<int, array<string, mixed>>
     */
    public function get(): array
    {
        [$sql, $bindings] = $this->compileSelect();

        $stmt = $this->connection->pdo()->prepare($sql);
        $stmt->execute($bindings);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get the first row or null.
     *
     * @return array<string, mixed>|null
     */
    public function first(): ?array
    {
        $this->limit(1);

        $rows = $this->get();

        return $rows[0] ?? null;
    }

    /**
     * Find by primary key "id".
     *
     * @param int|string $id
     */
    public function find(int|string $id): ?array
    {
        return $this->where('id', $id)->first();
    }

    /**
     * Insert a new row.
     *
     * @param array<string, mixed> $data
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);

        $placeholders = [];
        foreach ($columns as $column) {
            $placeholder = $this->makePlaceholder('ins_' . $column);
            $placeholders[] = ':' . $placeholder;
            $this->bindings[$placeholder] = $data[$column];
        }

        $columnsSql      = implode(', ', array_map(static fn ($c) => "`{$c}`", $columns));
        $placeholdersSql = implode(', ', $placeholders);

        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->table,
            $columnsSql,
            $placeholdersSql
        );

        $stmt = $this->connection->pdo()->prepare($sql);
        $stmt->execute($this->bindings);

        return (int) $this->connection->pdo()->lastInsertId();
    }

    /**
     * Update rows based on current WHERE conditions.
     *
     * @param array<string, mixed> $data
     */
    public function update(array $data): int
    {
        $setParts = [];

        foreach ($data as $column => $value) {
            $placeholder = $this->makePlaceholder('upd_' . $column);
            $setParts[]  = sprintf('`%s` = :%s', $column, $placeholder);
            $this->bindings[$placeholder] = $value;
        }

        $setSql = implode(', ', $setParts);
        $whereSql = $this->compileWhereClause();

        $sql = sprintf(
            'UPDATE `%s` SET %s%s',
            $this->table,
            $setSql,
            $whereSql
        );

        $stmt = $this->connection->pdo()->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    /**
     * Delete rows based on current WHERE conditions.
     */
    public function delete(): int
    {
        $whereSql = $this->compileWhereClause();

        $sql = sprintf('DELETE FROM `%s`%s', $this->table, $whereSql);

        $stmt = $this->connection->pdo()->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    /**
     * For debugging: see the SQL string.
     */
    public function toSql(): string
    {
        [$sql] = $this->compileSelect();

        return $sql;
    }

    /**
     * Build SELECT SQL and return [sql, bindings].
     *
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function compileSelect(): array
    {
        $columnsSql = implode(', ', $this->columns);

        $sql = sprintf('SELECT %s FROM `%s`', $columnsSql, $this->table);

        $sql .= $this->compileWhereClause();
        $sql .= $this->compileOrderClause();
        $sql .= $this->compileLimitOffsetClause();

        return [$sql, $this->bindings];
    }

    private function compileWhereClause(): string
    {
        if ($this->wheres === []) {
            return '';
        }

        $parts = [];

        foreach ($this->wheres as $index => $where) {
            $boolean = $index === 0 ? 'WHERE' : $where['boolean'];

            if (($where['type'] ?? null) === 'IN') {
                $placeholderList = implode(', ', $where['placeholders']);

                $parts[] = sprintf(
                    '%s `%s` IN (%s)',
                    $boolean,
                    $where['column'],
                    $placeholderList
                );
            } else {
                $parts[] = sprintf(
                    '%s `%s` %s :%s',
                    $boolean,
                    $where['column'],
                    $where['operator'],
                    $where['placeholder']
                );
            }
        }

        return ' ' . implode(' ', $parts);
    }

    private function compileOrderClause(): string
    {
        if ($this->orders === []) {
            return '';
        }

        $parts = [];

        foreach ($this->orders as $order) {
            $parts[] = sprintf('`%s` %s', $order['column'], $order['direction']);
        }

        return ' ORDER BY ' . implode(', ', $parts);
    }

    private function compileLimitOffsetClause(): string
    {
        $sql = '';

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    /**
     * Generate a unique placeholder key for bindings.
     */
    private function makePlaceholder(string $prefix): string
    {
        return $prefix . '_' . bin2hex(random_bytes(4));
    }
}
