<?php
/**
 * Migration script to add scans table
 * Run: php migrate_scans_to_db.php
 */

require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Read and execute the migration SQL
    $sql = file_get_contents(__DIR__ . '/add_scans_table.sql');
    
    // Execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }
    
    echo "Migration completed successfully!\n";
    echo "The 'scans' table has been created.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
