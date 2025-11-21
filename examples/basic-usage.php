<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use QueryLite\DB;

// 1. Connect to database (change credentials as needed)
DB::connect([
    'host'     => '127.0.0.1',
    'database' => 'querylite_demo',
    'username' => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
]);

echo "=== QueryLite Demo ===\n\n";

// 2. If no users exist, insert a demo user
$existing = DB::table('users')->limit(1)->get();

if (empty($existing)) {
    echo "No users found. Inserting a demo user...\n";

    $demoId = DB::table('users')->insert([
        'name'       => 'Demo User',
        'email'      => 'demo@example.com',
        'status'     => 'active',
        'created_at' => date('Y-m-d H:i:s'),
    ]);

    echo "Demo user inserted with ID: {$demoId}\n\n";
}

// 3. Simple SELECT – list active users
$users = DB::table('users')
    ->where('status', 'active')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

echo "Active users:\n";
print_r($users);
echo "\n";

// 4. Insert a new user
$newUserId = DB::table('users')->insert([
    'name'       => 'John Doe',
    'email'      => 'john@example.com',
    'status'     => 'active',
    'created_at' => date('Y-m-d H:i:s'),
]);

echo "Inserted user ID: {$newUserId}\n\n";

// 5. Find by ID (use the newly inserted one)
$user = DB::table('users')->find($newUserId);
echo "User with ID {$newUserId} (from find()):\n";
print_r($user);
echo "\n";

// 6. Update that user
$updatedRows = DB::table('users')
    ->where('id', $newUserId)
    ->update([
        'status' => 'inactive',
    ]);

echo "Updated rows (set status = 'inactive'): {$updatedRows}\n\n";

// 7. Show inactive users to prove update worked
$inactiveUsers = DB::table('users')
    ->where('status', 'inactive')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

echo "Recently inactive users:\n";
print_r($inactiveUsers);
echo "\n";

// 8. (Optional) Comment this out if you want to KEEP the data
// $deletedRows = DB::table('users')
//     ->where('id', $newUserId)
//     ->delete();
//
// echo "Deleted rows: {$deletedRows}\n";
