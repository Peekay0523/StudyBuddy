<?php
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

$stmt = $db->query('SELECT id, message_type, LENGTH(voice_data) as data_len FROM study_group_messages WHERE message_type = "voice"');

echo "Voice messages in database:\n";
echo str_repeat('-', 50) . "\n";

while ($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . ", Type: " . $row['message_type'] . ", Data Length: " . ($row['data_len'] ?? 0) . " bytes\n";
}

echo str_repeat('-', 50) . "\n";

// Also check all messages
$stmt = $db->query('SELECT id, message_type, file_path FROM study_group_messages ORDER BY id DESC LIMIT 10');
echo "\nLast 10 messages:\n";
echo str_repeat('-', 50) . "\n";

while ($row = $stmt->fetch()) {
    echo "ID: " . $row['id'] . ", Type: " . $row['message_type'] . ", File: " . ($row['file_path'] ?? 'NULL') . "\n";
}
