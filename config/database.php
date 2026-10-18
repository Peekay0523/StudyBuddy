<?php
/**
 * Database Configuration
 * PDO-based MySQL connection for School Learning Platform
 */

if (!defined('DB_HOST')) define('DB_HOST', '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', '3306');
if (!defined('DB_NAME')) define('DB_NAME', 'studybuddy');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', 'root');
if (!defined('DB_CHARSET')) define('DB_CHARSET', 'utf8mb4');

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            // Relax strict mode for legacy (SQLite-era) queries/data
            $this->connection->exec("SET SESSION sql_mode='NO_ENGINE_SUBSTITUTION'");
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

/**
 * Return the list of column names for a table.
 * Portable replacement for the SQLite "PRAGMA table_info(table)" pattern.
 */
if (!function_exists('db_table_columns')) {
    function db_table_columns($db, $table) {
        $table = str_replace('`', '', $table);
        $rows = $db->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        return array_map(function ($r) { return $r['Field']; }, $rows);
    }
}

/**
 * Idempotently create an index if it does not already exist.
 * Portable replacement for the SQLite "CREATE INDEX IF NOT EXISTS" pattern,
 * which MySQL does not support.
 */
if (!function_exists('db_create_index')) {
    function db_create_index($db, $indexName, $table, $columns, $unique = false) {
        try {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM information_schema.statistics
                WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?
            ");
            $stmt->execute([$table, $indexName]);
            if ((int)$stmt->fetchColumn() > 0) {
                return;
            }
            $uniqueSql = $unique ? 'UNIQUE ' : '';
            $db->exec("CREATE {$uniqueSql}INDEX `$indexName` ON `$table` ($columns)");
        } catch (PDOException $e) {
            // Index creation is best-effort; ignore if the table/columns are missing.
        }
    }
}
