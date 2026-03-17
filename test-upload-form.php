<?php
// Quick test: Check if upload form is working
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'models/Student.php';
require_once 'models/User.php';

session_start();

// Simulate being logged in as a student (remove this in production)
// $_SESSION['user_id'] = 1;
// $_SESSION['user'] = ['user_id' => 1, 'username' => 'test', 'role' => 'student'];

echo "<h1>Upload Script Test Page</h1>";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>NOT LOGGED IN. <a href='/login'>Login first</a></p>";
} else {
    echo "<p style='color: green;'>Logged in as: " . htmlspecialchars($_SESSION['user']['username']) . "</p>";
    
    // Try to get student info
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM students WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $student = $stmt->fetch();
        
        if ($student) {
            echo "<p>Student ID: " . $student['id'] . "</p>";
        } else {
            echo "<p style='color: red;'>No student record found for this user</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
    }
}

// Check configuration
echo "<h2>Configuration</h2>";
echo "UPLOAD_DIR_SCRIPTS: " . UPLOAD_DIR_SCRIPTS . "<br>";
echo "Directory exists: " . (is_dir(UPLOAD_DIR_SCRIPTS) ? 'YES' : 'NO') . "<br>";
echo "Directory writable: " . (is_writable(UPLOAD_DIR_SCRIPTS) ? 'YES' : 'NO') . "<br>";
echo "ALLOWED_SCRIPT_EXTENSIONS: " . implode(', ', ALLOWED_SCRIPT_EXTENSIONS) . "<br>";
echo "MAX_FILE_SIZE: " . MAX_FILE_SIZE . " bytes<br>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "post_max_size: " . ini_get('post_max_size') . "<br>";
?>

<h2>Test Upload Form</h2>
<form method="post" action="/upload-script" enctype="multipart/form-data">
    <div>
        <label>Select file:</label><br>
        <input type="file" name="script_file" accept=".pdf,.docx,.txt" required>
    </div>
    <div>
        <label>Title:</label><br>
        <input type="text" name="title" value="Test Upload">
    </div>
    <div>
        <label>Subject:</label><br>
        <input type="text" name="subject" value="Test">
    </div>
    <div>
        <label>Grade Level:</label><br>
        <select name="grade_level">
            <option value="10">Grade 10</option>
            <option value="11">Grade 11</option>
            <option value="12">Grade 12</option>
        </select>
    </div>
    <button type="submit">Upload</button>
</form>
