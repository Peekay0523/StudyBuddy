<?php
/**
 * Migration: Add model_used column to openai_usage_logs table
 * This enables tracking which AI model (Grok vs OpenAI) was used for each request
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/helpers/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if column already exists
    $columns = $db->query("PRAGMA table_info(openai_usage_logs)")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('model_used', $columns)) {
        // Add model_used column
        $db->exec("ALTER TABLE openai_usage_logs ADD COLUMN model_used TEXT DEFAULT 'openai'");
        echo "Added model_used column to openai_usage_logs table\n";
    } else {
        echo "model_used column already exists\n";
    }
    
    // Create index if it doesn't exist
    try {
        $db->exec("CREATE INDEX IF NOT EXISTS idx_usage_model_used ON openai_usage_logs(model_used)");
        echo "Created index on model_used column\n";
    } catch (Exception $e) {
        echo "Index already exists or creation failed: " . $e->getMessage() . "\n";
    }
    
    // Update existing records
    $db->exec("UPDATE openai_usage_logs SET model_used = 'openai' WHERE model_used IS NULL");
    echo "Updated existing records to use 'openai' as default\n";
    
    echo "Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
