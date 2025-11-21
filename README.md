# 📦 QueryLite – Lightweight PHP Query Builder & Mini ORM

**QueryLite** is a simple, elegant, and educational **PHP Query Builder / mini ORM** built from scratch.  
It demonstrates core backend concepts such as query abstraction, prepared statements, fluent APIs, and database architecture — making it an excellent **portfolio project**.

## 🚀 Features

- 🔹 Fluent, chainable query builder  
- 🔹 Supports `select`, `where`, `orWhere`, `whereIn`  
- 🔹 Supports `orderBy`, `limit`, `offset`  
- 🔹 CRUD operations:
  - `insert()`
  - `update()`
  - `delete()`
- 🔹 `first()` and `find(id)` helpers  
- 🔹 PDO-powered prepared statements (SQL injection safe)  
- 🔹 PSR-4 autoloading via Composer  
- 🔹 Clean, minimal PHP 8+ codebase  

## 📁 Folder Structure

```
querylite/
│── src/
│   ├── Core/
│   │    ├── Connection.php
│   │    └── QueryBuilder.php
│   └── DB.php
│
│── examples/
│   └── basic-usage.php
│
│── composer.json
│── README.md
```

## 🛠 Requirements

- PHP **8.1+**
- MySQL / MariaDB
- PDO extension enabled  
- Composer

## ⚙️ Installation

Clone the repository:

```bash
git clone https://github.com/harkiratbajwa/querylite.git
cd querylite
composer install
```

Or include it in another project via Composer:

```bash
composer require harkiratbajwa/querylite
```

## 🧩 Basic Usage

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use QueryLite\DB;

// Connect to the database
DB::connect([
    'host'     => '127.0.0.1',
    'database' => 'my_database',
    'username' => 'root',
    'password' => '',
]);

// Example: Get all active users
$users = DB::table('users')
    ->where('status', 'active')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

print_r($users);

// Example: Find a user by ID
$user = DB::table('users')->find(5);
print_r($user);
```

## 📘 Example Database Table

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(150),
    status VARCHAR(20),
    created_at DATETIME
);
```

## 🧪 Demo Script (Included)

You can test QueryLite with:

```
php examples/basic-usage.php
```

This demonstrates:

- Select  
- Insert  
- Find  
- Update  
- Delete  

## 🎯 Why This Project Exists

QueryLite was built to:

- Understand how Query Builders & ORMs work internally  
- Demonstrate backend engineering skills  
- Build reusable PHP architecture  
- Provide clear, readable source code for learning  

Use it in:

- Portfolio projects  
- Learning PDO  
- Practicing database abstraction  
- Lightweight custom applications  

## 📄 License

MIT License — free to use, modify, and distribute.

## 🤝 Contributing

Pull requests are welcome!
