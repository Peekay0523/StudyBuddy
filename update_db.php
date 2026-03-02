<?php
// Script to update database schema
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance()->getConnection();

echo "Updating database schema...\n\n";

// Add role column to users table
$usersColumns = $db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC);
$usersColumnNames = array_column($usersColumns, 'name');
if (!in_array('role', $usersColumnNames)) {
    $db->exec("ALTER TABLE users ADD COLUMN role TEXT DEFAULT 'student'");
    echo "Added role column to users table\n";
} else {
    echo "role column already exists in users table\n";
}

// Create scripts table if not exists
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS scripts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            file_name TEXT NOT NULL,
            file_path TEXT NOT NULL,
            file_size INTEGER DEFAULT 0,
            subject TEXT DEFAULT '',
            memorandum_generated INTEGER DEFAULT 0,
            uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "Created scripts table\n";
} catch (PDOException $e) {
    echo "scripts table already exists\n";
}

// Create subscriptions table if not exists
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            plan TEXT NOT NULL,
            price REAL NOT NULL,
            status TEXT DEFAULT 'active',
            current_period_start DATETIME,
            current_period_end DATETIME,
            cancelled_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )
    ");
    echo "Created subscriptions table\n";
} catch (PDOException $e) {
    echo "subscriptions table already exists\n";
}

// Add columns to report_cards table
$reportCardsColumns = $db->query("PRAGMA table_info(report_cards)")->fetchAll(PDO::FETCH_ASSOC);
$reportCardsColumnNames = array_column($reportCardsColumns, 'name');

if (!in_array('user_id', $reportCardsColumnNames)) {
    try {
        $db->exec("ALTER TABLE report_cards ADD COLUMN user_id INTEGER");
        echo "Added user_id column to report_cards table\n";
    } catch (PDOException $e) {
        echo "user_id column already exists in report_cards table\n";
    }
} else {
    echo "user_id column already exists in report_cards table\n";
}
if (!in_array('file_name', $reportCardsColumnNames)) {
    try {
        $db->exec("ALTER TABLE report_cards ADD COLUMN file_name TEXT");
        echo "Added file_name column to report_cards table\n";
    } catch (PDOException $e) {
        echo "file_name column already exists in report_cards table\n";
    }
} else {
    echo "file_name column already exists in report_cards table\n";
}
if (!in_array('average', $reportCardsColumnNames)) {
    try {
        $db->exec("ALTER TABLE report_cards ADD COLUMN average REAL DEFAULT 0");
        echo "Added average column to report_cards table\n";
    } catch (PDOException $e) {
        echo "average column already exists in report_cards table\n";
    }
} else {
    echo "average column already exists in report_cards table\n";
}
if (!in_array('career_recommendations_generated', $reportCardsColumnNames)) {
    try {
        $db->exec("ALTER TABLE report_cards ADD COLUMN career_recommendations_generated INTEGER DEFAULT 0");
        echo "Added career_recommendations_generated column to report_cards table\n";
    } catch (PDOException $e) {
        echo "career_recommendations_generated column already exists\n";
    }
} else {
    echo "career_recommendations_generated column already exists in report_cards table\n";
}

// Add columns to career_recommendations table
$careerColumns = $db->query("PRAGMA table_info(career_recommendations)")->fetchAll(PDO::FETCH_ASSOC);
$careerColumnNames = array_column($careerColumns, 'name');
if (!in_array('courses_data', $careerColumnNames)) {
    try {
        $db->exec("ALTER TABLE career_recommendations ADD COLUMN courses_data TEXT DEFAULT '[]'");
        echo "Added courses_data column to career_recommendations\n";
    } catch (PDOException $e) {
        echo "courses_data column already exists in career_recommendations\n";
    }
} else {
    echo "courses_data column already exists in career_recommendations\n";
}
if (!in_array('bursaries_data', $careerColumnNames)) {
    try {
        $db->exec("ALTER TABLE career_recommendations ADD COLUMN bursaries_data TEXT DEFAULT '[]'");
        echo "Added bursaries_data column to career_recommendations\n";
    } catch (PDOException $e) {
        echo "bursaries_data column already exists in career_recommendations\n";
    }
} else {
    echo "bursaries_data column already exists in career_recommendations\n";
}

echo "\nDatabase update complete!\n";
?>
