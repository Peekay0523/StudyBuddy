<?php
/**
 * Debug: Check database structure and data
 */

require_once __DIR__ . '/config/database.php';

echo "<h2>Database Debug Information</h2>";

$db = Database::getInstance()->getConnection();

// Check report_cards table structure
echo "<h3>report_cards table structure</h3>";
echo "<pre>";
$columns = $db->query("PRAGMA table_info(report_cards)")->fetchAll();
print_r($columns);
echo "</pre>";

// Check scripts table structure
echo "<h3>scripts table structure</h3>";
echo "<pre>";
$columns = $db->query("PRAGMA table_info(scripts)")->fetchAll();
print_r($columns);
echo "</pre>";

// Check uploaded_scripts table structure
echo "<h3>uploaded_scripts table structure</h3>";
echo "<pre>";
$columns = $db->query("PRAGMA table_info(uploaded_scripts)")->fetchAll();
print_r($columns);
echo "</pre>";

// Count records in each table
echo "<h3>Record Counts</h3>";
echo "<ul>";
echo "<li>scripts: " . $db->query("SELECT COUNT(*) FROM scripts")->fetchColumn() . "</li>";
echo "<li>uploaded_scripts: " . $db->query("SELECT COUNT(*) FROM uploaded_scripts")->fetchColumn() . "</li>";
echo "<li>report_cards: " . $db->query("SELECT COUNT(*) FROM report_cards")->fetchColumn() . "</li>";
echo "<li>scans: " . $db->query("SELECT COUNT(*) FROM scans")->fetchColumn() . "</li>";
echo "</ul>";

// Show recent records
echo "<h3>Recent Scripts (scripts table)</h3>";
echo "<pre>";
$scripts = $db->query("SELECT * FROM scripts ORDER BY uploaded_at DESC LIMIT 5")->fetchAll();
print_r($scripts);
echo "</pre>";

echo "<h3>Recent Uploaded Scripts (uploaded_scripts table)</h3>";
echo "<pre>";
$uploadedScripts = $db->query("SELECT * FROM uploaded_scripts ORDER BY uploaded_at DESC LIMIT 5")->fetchAll();
print_r($uploadedScripts);
echo "</pre>";

echo "<h3>Recent Report Cards</h3>";
echo "<pre>";
$reportCards = $db->query("SELECT * FROM report_cards ORDER BY uploaded_at DESC LIMIT 5")->fetchAll();
print_r($reportCards);
echo "</pre>";

echo "<h3>Recent Scans</h3>";
echo "<pre>";
$scans = $db->query("SELECT * FROM scans ORDER BY created_at DESC LIMIT 5")->fetchAll();
print_r($scans);
echo "</pre>";
