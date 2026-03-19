<?php
/**
 * Test download for memorandum
 */
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/auth_helper.php';
require_once __DIR__ . '/helpers/session_helper.php';
require_once __DIR__ . '/models/Script.php';
require_once __DIR__ . '/models/Memorandum.php';

// Test with script ID 19
$scriptId = 19;

$db = Database::getInstance()->getConnection();

// Get script
$stmt = $db->prepare("SELECT * FROM scripts WHERE id = ?");
$stmt->execute([$scriptId]);
$script = $stmt->fetch();

echo "<h2>Script Data</h2>";
echo "<pre>";
print_r($script);
echo "</pre>";

// Get memorandum
$stmt = $db->prepare("SELECT * FROM memorandums WHERE script_id = ?");
$stmt->execute([$scriptId]);
$memorandum = $stmt->fetch();

echo "<h2>Memorandum Data</h2>";
echo "<pre>";
print_r($memorandum);
echo "</pre>";

if ($memorandum) {
    echo "<h2>Memorandum Content (first 500 chars)</h2>";
    echo "<pre>" . htmlspecialchars(substr($memorandum['content'], 0, 500)) . "...</pre>";
    
    echo "<h2>Test Links</h2>";
    echo "<a href='/download-memorandum/$scriptId?format=pdf'>Download PDF</a><br>";
    echo "<a href='/download-memorandum/$scriptId?format=docx'>Download DOCX</a>";
}
?>
