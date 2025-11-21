# QueryLite – Tiny PHP Query Builder

QueryLite is a small, educational **query builder / mini ORM** for PHP and MySQL.
It is built to understand how ORMs like Eloquent work under the hood and to
show clean backend architecture skills in a portfolio.

## Features

- Simple static `DB` facade
- `DB::table('users')->where(...)->get()` style syntax
- Supports:
  - `select`, `where`, `orWhere`, `whereIn`
  - `orderBy`, `limit`, `offset`
  - `insert`, `update`, `delete`
  - `first()`, `find($id)`
- Uses **prepared statements** (PDO) for safety
- PSR-4 autoloading

## Requirements

- PHP 8.1+
- MySQL
- PDO extension (`ext-pdo`)

## Installation

Clone the repository:

```bash
git clone https://github.com/yourname/querylite.git
cd querylite
composer install
```

Or include it in another project via Composer:

```bash
composer require yourname/querylite
```

## Basic Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use QueryLite\DB;

DB::connect([
    'host'     => '127.0.0.1',
    'database' => 'my_database',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
]);

// Get all active users
$users = DB::table('users')
    ->where('status', 'active')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Insert
$id = DB::table('users')->insert([
    'name'       => 'John Doe',
    'email'      => 'john@example.com',
    'status'     => 'active',
    'created_at' => date('Y-m-d H:i:s'),
]);
```

## Example Database Table

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(150),
    status VARCHAR(20),
    created_at DATETIME
);
```

## Purpose

This library is intentionally small and not meant to replace full-featured ORMs.
It is designed for:

- Learning how query builders work
- Showcasing PHP + PDO + SQL skills
- Using as a base for experiments and side projects
