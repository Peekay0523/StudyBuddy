<?php
/**
 * Debug memorandum download
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/session_helper.php';
require_once __DIR__ . '/helpers/auth_helper.php';
require_once __DIR__ . '/models/Script.php';
require_once __DIR__ . '/models/Memorandum.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$scriptId = 19;

try {
    $db = Database::getInstance()->getConnection();
    
    // Get script
    $stmt = $db->prepare("SELECT * FROM scripts WHERE id = ?");
    $stmt->execute([$scriptId]);
    $script = $stmt->fetch();
    
    if (!$script) {
        echo "ERROR: Script not found<br>";
        exit;
    }
    
    echo "✓ Script found: " . htmlspecialchars($script['title']) . "<br>";
    
    // Get memorandum
    $stmt = $db->prepare("SELECT * FROM memorandums WHERE script_id = ?");
    $stmt->execute([$scriptId]);
    $memorandum = $stmt->fetch();
    
    if (!$memorandum) {
        echo "ERROR: Memorandum not found for script ID $scriptId<br>";
        exit;
    }
    
    echo "✓ Memorandum found (content length: " . strlen($memorandum['content']) . " chars)<br>";
    
    // Test HTML generation
    $content = $memorandum['content'];
    $lines = explode("\n", $content);
    $formattedContent = '';
    
    foreach ($lines as $line) {
        $trimmedLine = trim($line);
        if (empty($trimmedLine)) {
            $formattedContent .= '<br>';
        } elseif (strpos($trimmedLine, '# ') === 0) {
            $formattedContent .= '<h2>' . htmlspecialchars(substr($trimmedLine, 2)) . '</h2>';
        } elseif (strpos($trimmedLine, '## ') === 0) {
            $formattedContent .= '<h3>' . htmlspecialchars(substr($trimmedLine, 3)) . '</h3>';
        } elseif (strpos($trimmedLine, '- ') === 0 || strpos($trimmedLine, '* ') === 0) {
            $formattedContent .= '<li>' . htmlspecialchars(substr($trimmedLine, 2)) . '</li>';
        } else {
            $formattedContent .= '<p>' . htmlspecialchars($trimmedLine) . '</p>';
        }
    }
    
    $formattedContent = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $formattedContent);
    $formattedContent = str_replace('</ul><ul>', '', $formattedContent);
    
    $htmlContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test Memorandum</title>
</head>
<body>
    <h1>Memorandum Test</h1>
    <p>Script: ${script['title']}</p>
    $formattedContent
</body>
</html>
HTML;
    
    echo "✓ HTML generated successfully<br>";
    echo "<hr>";
    echo "<a href='/download-memorandum/$scriptId?format=pdf'>Download PDF</a> | ";
    echo "<a href='/download-memorandum/$scriptId?format=docx'>Download DOCX</a>";
    echo "<hr>";
    echo "<h3>Preview (first 1000 chars):</h3>";
    echo substr($htmlContent, 0, 1000);
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
