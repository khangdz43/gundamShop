<?php
require_once __DIR__ . '/../config/db.php';

$columns = [
    'remember_token' => "ALTER TABLE users ADD COLUMN remember_token VARCHAR(64) DEFAULT NULL",
    'remember_expires' => "ALTER TABLE users ADD COLUMN remember_expires DATETIME DEFAULT NULL",
];

foreach ($columns as $name => $sql) {
    $result = $conn->query("SHOW COLUMNS FROM users LIKE '$name'");
    if ($result && $result->num_rows === 0) {
        $conn->query($sql);
        echo "Added column: $name\n";
    } else {
        echo "Column exists: $name\n";
    }
    if ($result) $result->free();
}

echo "Migration complete.\n";
