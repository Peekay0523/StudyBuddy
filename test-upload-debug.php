<?php
/**
 * Debug upload form issue
 */
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

echo "<h1>Upload Debug Info</h1>";

echo "<h2>Configuration</h2>";
echo "<pre>";
echo "UPLOAD_DIR_SCRIPTS: " . (defined('UPLOAD_DIR_SCRIPTS') ? UPLOAD_DIR_SCRIPTS : 'NOT DEFINED') . "\n";
echo "Directory exists: " . (is_dir(UPLOAD_DIR_SCRIPTS) ? 'YES' : 'NO') . "\n";
echo "Directory writable: " . (is_writable(UPLOAD_DIR_SCRIPTS) ? 'YES' : 'NO') . "\n";
echo "ALLOWED_SCRIPT_EXTENSIONS: " . json_encode(ALLOWED_SCRIPT_EXTENSIONS) . "\n";
echo "MAX_FILE_SIZE: " . MAX_FILE_SIZE . "\n";
echo "</pre>";

echo "<h2>POST Data</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h2>FILES Data</h2>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

echo "<h2>Session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<h2>Server</h2>";
echo "<pre>";
echo "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? 'NOT SET') . "\n";
echo "CONTENT_TYPE: " . ($_SERVER['CONTENT_TYPE'] ?? 'NOT SET') . "\n";
echo "</pre>";

// Test file write
echo "<h2>Test File Write</h2>";
$testFile = UPLOAD_DIR_SCRIPTS . 'test_' . time() . '.txt';
$testResult = file_put_contents($testFile, 'Test content');
echo "Write test: " . ($testResult ? 'SUCCESS' : 'FAILED') . "\n";
if ($testResult) {
    unlink($testFile);
    echo "Test file cleaned up\n";
}

// Check if student is logged in
echo "<h2>Student Check</h2>";
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "\n";
    echo "User Role: " . ($_SESSION['user']['role'] ?? 'NOT SET') . "\n";
    
    if (isset($_SESSION['student'])) {
        echo "Student ID: " . $_SESSION['student']['id'] . "\n";
        echo "Student User ID: " . $_SESSION['student']['user_id'] . "\n";
    } else {
        echo "Student session NOT SET\n";
        
        // Try to fetch student
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM students WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $student = $stmt->fetch();
        if ($student) {
            echo "Student found in DB: " . $student['id'] . "\n";
            $_SESSION['student'] = $student;
        } else {
            echo "Student NOT found in database\n";
        }
    }
} else {
    echo "User NOT logged in\n";
}

echo "<h2>Form Test</h2>";
echo "<form method='post' action='/upload-script' enctype='multipart/form-data' style='border: 2px solid blue; padding: 20px; margin: 20px;'>";
echo "<input type='file' name='script_file' accept='.pdf,.docx,.txt'><br>";
echo "<input type='text' name='subject' placeholder='Subject' required><br>";
echo "<select name='grade_level' required>";
echo "<option value=''>Select Grade</option>";
echo "<option value='10'>Grade 10</option>";
echo "</select><br>";
echo "<button type='submit'>Test Upload</button>";
echo "</form>";

echo "<h2>JavaScript Console Test</h2>";
echo "<div id='debug-output' style='border: 2px solid green; padding: 10px; margin: 10px;'></div>";
echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    const debugOutput = document.getElementById('debug-output');
    debugOutput.innerHTML += '<p>DOMContentLoaded fired</p>';
    
    const form = document.querySelector('form[method=\"post\"][action=\"/upload-script\"]');
    debugOutput.innerHTML += '<p>Form found: ' + (form ? 'YES' : 'NO') + '</p>';
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const fileInput = document.getElementById('script_file');
            debugOutput.innerHTML += '<p>Form submit event fired</p>';
            debugOutput.innerHTML += '<p>File input exists: ' + (fileInput ? 'YES' : 'NO') + '</p>';
            if (fileInput) {
                debugOutput.innerHTML += '<p>Files count: ' + fileInput.files.length + '</p>';
                debugOutput.innerHTML += '<p>File input value: ' + fileInput.value + '</p>';
            }
        });
    }
});
</script>";
