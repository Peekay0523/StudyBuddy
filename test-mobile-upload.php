<?php
/**
 * Mobile Scan Upload Diagnostic Test
 * This page helps identify why photos taken on mobile aren't uploading
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Only allow logged-in users
requireLogin();

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Scan Diagnostic Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #667eea; padding-bottom: 10px; }
        h2 { color: #667eea; margin-top: 30px; }
        .test-item { padding: 15px; margin: 10px 0; border-radius: 6px; }
        .pass { background: #d1fae5; border-left: 4px solid #10b981; }
        .fail { background: #fee2e2; border-left: 4px solid #ef4444; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; }
        .info { background: #dbeafe; border-left: 4px solid #3b82f6; }
        pre { background: #f1f5f9; padding: 15px; border-radius: 6px; overflow-x: auto; font-size: 12px; }
        .upload-form { margin: 20px 0; padding: 20px; background: #f8fafc; border: 2px dashed #cbd5e1; border-radius: 8px; }
        button { background: #667eea; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 16px; }
        button:hover { background: #5568d3; }
        #result { margin-top: 20px; padding: 15px; border-radius: 6px; display: none; }
        .log-entry { font-family: monospace; font-size: 11px; padding: 2px 0; border-bottom: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 Mobile Scan Upload Diagnostic Test</h1>
    <p>User: <strong><?php echo htmlspecialchars($user['username']); ?></strong> (ID: <?php echo $user['id']; ?>)</p>

    <h2>1. PHP Configuration Tests</h2>

    <?php
    $tests = [];

    // Test 1: File uploads enabled
    $uploadsEnabled = ini_get('file_uploads');
    $tests[] = [
        'name' => 'File Uploads Enabled',
        'status' => $uploadsEnabled ? 'pass' : 'fail',
        'message' => $uploadsEnabled ? 'Yes' : 'No - Uploads are disabled!'
    ];

    // Test 2: Upload max filesize
    $uploadMax = ini_get('upload_max_filesize');
    $uploadMaxBytes = parseSize($uploadMax);
    $tests[] = [
        'name' => 'Upload Max Filesize',
        'status' => $uploadMaxBytes >= 5 * 1024 * 1024 ? 'pass' : 'warning',
        'message' => $uploadMax . ' (Mobile photos can be 5-10MB)'
    ];

    // Test 3: Post max size
    $postMax = ini_get('post_max_size');
    $postMaxBytes = parseSize($postMax);
    $tests[] = [
        'name' => 'Post Max Size',
        'status' => $postMaxBytes >= 10 * 1024 * 1024 ? 'pass' : 'warning',
        'message' => $postMax . ' (Should be larger than upload_max_filesize)'
    ];

    // Test 4: Max file uploads
    $maxFileUploads = ini_get('max_file_uploads');
    $tests[] = [
        'name' => 'Max File Uploads',
        'status' => $maxFileUploads >= 10 ? 'pass' : 'warning',
        'message' => $maxFileUploads . ' files'
    ];

    // Test 5: Memory limit
    $memoryLimit = ini_get('memory_limit');
    $memoryBytes = parseSize($memoryLimit);
    $tests[] = [
        'name' => 'Memory Limit',
        'status' => $memoryBytes >= 128 * 1024 * 1024 ? 'pass' : 'warning',
        'message' => $memoryLimit . ' (PDF generation needs memory)'
    ];

    // Test 6: Max execution time
    $maxExecutionTime = ini_get('max_execution_time');
    $tests[] = [
        'name' => 'Max Execution Time',
        'status' => $maxExecutionTime >= 30 ? 'pass' : 'warning',
        'message' => $maxExecutionTime . ' seconds'
    ];

    // Test 7: Upload tmp dir
    $uploadTmpDir = ini_get('upload_tmp_dir');
    $tmpDirWritable = $uploadTmpDir && is_writable($uploadTmpDir);
    $tests[] = [
        'name' => 'Upload Temp Directory',
        'status' => $tmpDirWritable ? 'pass' : ($uploadTmpDir ? 'warning' : 'info'),
        'message' => $uploadTmpDir ?: 'Using system default'
    ];

    // Test 8: Session check
    $sessionStatus = session_status() === PHP_SESSION_ACTIVE;
    $tests[] = [
        'name' => 'Session Status',
        'status' => $sessionStatus ? 'pass' : 'fail',
        'message' => $sessionStatus ? 'Active' : 'Not active'
    ];

    // Test 9: Database connection
    try {
        $db = Database::getInstance()->getConnection();
        $db->query("SELECT 1");
        $tests[] = [
            'name' => 'Database Connection',
            'status' => 'pass',
            'message' => 'Connected successfully'
        ];
    } catch (Exception $e) {
        $tests[] = [
            'name' => 'Database Connection',
            'status' => 'fail',
            'message' => 'Failed: ' . $e->getMessage()
        ];
    }

    // Test 10: Scans table exists
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM scans");
        $count = $stmt->fetchColumn();
        $tests[] = [
            'name' => 'Scans Table',
            'status' => 'pass',
            'message' => "Exists ({$count} scans in database)"
        ];
    } catch (Exception $e) {
        $tests[] = [
            'name' => 'Scans Table',
            'status' => 'fail',
            'message' => 'Missing or error: ' . $e->getMessage()
        ];
    }

    // Display test results
    foreach ($tests as $test) {
        $class = $test['status'];
        $icon = $test['status'] === 'pass' ? '✓' : ($test['status'] === 'fail' ? '✗' : '⚠');
        echo "<div class='test-item {$class}'>";
        echo "<strong>{$icon} {$test['name']}:</strong> {$test['message']}";
        echo "</div>";
    }
    ?>

    <h2>2. Mobile Upload Test</h2>
    <div class="upload-form info test-item">
        <p><strong>Instructions:</strong></p>
        <ol>
            <li>Open this page on your mobile device</li>
            <li>Click "Take Photo" and capture an image</li>
            <li>Click "Upload Test Image"</li>
            <li>Check the results below</li>
        </ol>

        <form id="uploadForm" method="POST" enctype="multipart/form-data" style="margin-top: 15px;">
            <input type="file" name="test_image" id="testImage" accept="image/*" capture="environment" required style="margin-bottom: 10px;">
            <br>
            <button type="submit">📸 Upload Test Image</button>
        </form>

        <div id="result"></div>
    </div>

    <h2>3. JavaScript Console Log</h2>
    <div id="console-log" style="background: #1e293b; color: #10b981; padding: 15px; border-radius: 6px; max-height: 300px; overflow-y: auto;">
        <div class="log-entry">Waiting for upload attempt...</div>
    </div>

    <h2>4. Scan Usage Info</h2>
    <?php
    $scanInfo = getScanLimitInfo($user['id']);
    ?>
    <div class="info test-item">
        <p><strong>Free Tier:</strong> <?php echo $scanInfo['is_free_tier'] ? 'Yes' : 'No'; ?></p>
        <p><strong>Scan Limit:</strong> <?php echo $scanInfo['limit']; ?></p>
        <p><strong>Scans Used:</strong> <?php echo $scanInfo['used']; ?></p>
        <p><strong>Scans Remaining:</strong> <?php echo $scanInfo['remaining']; ?></p>
        <?php if ($scanInfo['period_end']): ?>
        <p><strong>Period Ends:</strong> <?php echo date('M d, Y', strtotime($scanInfo['period_end'])); ?></p>
        <?php endif; ?>
    </div>
</div>

<script>
const form = document.getElementById('uploadForm');
const resultDiv = document.getElementById('result');
const consoleLog = document.getElementById('console-log');

function log(message, type = 'info') {
    const entry = document.createElement('div');
    entry.className = 'log-entry';
    const time = new Date().toLocaleTimeString();
    const color = type === 'error' ? '#ef4444' : (type === 'success' ? '#10b981' : '#10b981');
    entry.style.color = color;
    entry.textContent = `[${time}] ${message}`;
    consoleLog.appendChild(entry);
    consoleLog.scrollTop = consoleLog.scrollHeight;
    console.log(message);
}

log('Diagnostic page loaded');
log('User ID: <?php echo $user['id']; ?>');

form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const fileInput = document.getElementById('testImage');
    const file = fileInput.files[0];

    if (!file) {
        log('No file selected', 'error');
        return;
    }

    log(`File selected: ${file.name}`);
    log(`File type: ${file.type}`);
    log(`File size: ${(file.size / 1024 / 1024).toFixed(2)} MB`);

    const formData = new FormData();
    formData.append('test_image', file);

    resultDiv.style.display = 'block';
    resultDiv.className = 'info test-item';
    resultDiv.innerHTML = '<strong>Uploading...</strong> Please wait';
    log('Starting upload to /test-mobile-upload');

    try {
        const response = await fetch('/test-mobile-upload', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        log(`Response status: ${response.status}`);
        log(`Response headers: ${[...response.headers.entries()].map(h => h[0] + ': ' + h[1]).join(', ')}`);

        const contentType = response.headers.get('content-type');
        log(`Content-Type: ${contentType}`);

        let data;
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            log(`Raw response: ${text.substring(0, 500)}`, 'error');
            throw new Error('Non-JSON response: ' + text.substring(0, 200));
        }

        log(`Response data: ${JSON.stringify(data)}`);

        if (data.success) {
            resultDiv.className = 'pass test-item';
            resultDiv.innerHTML = `
                <strong>✓ Upload Successful!</strong><br>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            `;
            log('Upload successful!', 'success');
        } else {
            resultDiv.className = 'fail test-item';
            resultDiv.innerHTML = `
                <strong>✗ Upload Failed</strong><br>
                <p>Error: ${data.error || 'Unknown error'}</p>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            `;
            log(`Upload failed: ${data.error}`, 'error');
        }
    } catch (error) {
        resultDiv.className = 'fail test-item';
        resultDiv.innerHTML = `
            <strong>✗ Upload Error</strong><br>
            <p>${error.message}</p>
        `;
        log(`Upload error: ${error.message}`, 'error');
    }
});
</script>

<?php
function parseSize($size) {
    $unit = strtoupper(substr($size, -1));
    $num = intval(substr($size, 0, -1));
    switch ($unit) {
        case 'G': return $num * 1024 * 1024 * 1024;
        case 'M': return $num * 1024 * 1024;
        case 'K': return $num * 1024;
        default: return $num;
    }
}
?>

<?php
// This page is for diagnostic display only
// Uploads are handled via AJAX to /test-mobile-upload
?>

</body>
</html>
