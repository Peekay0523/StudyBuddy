<?php
require_once 'config/database.php';

$db = Database::getInstance()->getConnection();
$stmt = $db->query('PRAGMA table_info(uploaded_scripts)');

echo "Columns in uploaded_scripts table:\n";
echo str_repeat("-", 50) . "\n";

while ($row = $stmt->fetch()) {
    echo sprintf("%-25s %-10s %s\n", $row['name'], $row['type'], $row['notnull'] ? 'NOT NULL' : '');
}

echo str_repeat("-", 50) . "\n";
