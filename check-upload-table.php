<?php
require_once 'config/database.php';

$db = Database::getInstance()->getConnection();
$result = $db->query('SELECT name FROM sqlite_master WHERE type="table" AND name="uploaded_scripts"');
echo "Table exists: ";
var_dump($result->fetch());

echo "\nTable schema:\n";
$result = $db->query('PRAGMA table_info(uploaded_scripts)');
while ($row = $result->fetch()) {
    print_r($row);
}
