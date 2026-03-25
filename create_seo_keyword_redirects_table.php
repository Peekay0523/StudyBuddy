<?php
/**
 * SEO Keyword Redirects Table Migration
 * Run this to create the tracking table for SEO keyword redirects
 * 
 * Usage: php create_seo_keyword_redirects_table.php
 */

require_once __DIR__ . '/config/database.php';

echo "==============================================\n";
echo "  SEO Keyword Redirects Table Migration\n";
echo "==============================================\n\n";

try {
    $db = Database::getInstance()->getConnection();
    echo "✓ Connected to database\n\n";

    // Read and execute the schema
    echo "Creating seo_keyword_redirects table...\n";
    $schemaFile = __DIR__ . '/create_seo_keyword_redirects_table.sql';

    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    $schema = file_get_contents($schemaFile);
    $statements = array_filter(array_map('trim', explode(';', $schema)));

    $tablesCreated = 0;
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            try {
                $db->exec($statement);
                $tablesCreated++;
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "  Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }

    echo "✓ Created/Updated $tablesCreated table(s)/index(es)\n\n";

    echo "==============================================\n";
    echo "  Migration Complete!\n";
    echo "==============================================\n\n";
    echo "The seo_keyword_redirects table is now ready.\n";
    echo "SEO keyword redirects will be tracked automatically.\n\n";

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
