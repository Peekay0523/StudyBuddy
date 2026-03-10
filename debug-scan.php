<?php
/**
 * Debug Scan PDF
 * Access: http://localhost:8000/debug-scan/{id}
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Only allow admin or debug mode
if (!DEBUG_MODE && (!isLoggedIn() || getCurrentUser()['role'] !== 'admin')) {
    die('Access denied');
}

$scanId = $_GET['id'] ?? ($_SERVER['PATH_INFO'] ?? '');
$scanId = str_replace('/', '', $scanId);

if (empty($scanId)) {
    die('Please provide scan ID: /debug-scan/{id}');
}

$db = Database::getInstance()->getConnection();

// Get scan from database
$stmt = $db->prepare("SELECT * FROM scans WHERE id = ?");
$stmt->execute([$scanId]);
$scan = $stmt->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Scan #<?php echo $scanId; ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <style>
        .debug-container {
            max-width: 900px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .info-item {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .info-label {
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
        }
        .info-value {
            font-size: 16px;
            color: #1e293b;
            font-weight: 500;
            margin-top: 5px;
            word-break: break-all;
        }
        .status-success {
            background: #f0fdf4;
            color: #16a34a;
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #bbf7d0;
        }
        .status-error {
            background: #fef2f2;
            color: #dc2626;
            padding: 10px 15px;
            border-radius: 6px;
            border: 1px solid #fecaca;
        }
        .code-block {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            margin-right: 10px;
        }
    </style>
</head>
<body>

<div class="debug-container">
    <h1 style="color: #1f2937;">
        <i class="fas fa-bug"></i> Debug Scan #<?php echo $scanId; ?>
    </h1>

    <?php if (!$scan): ?>
        <div class="status-error">
            <i class="fas fa-times-circle"></i>
            <strong>Scan not found in database!</strong>
        </div>
    <?php else: ?>
        <div class="status-success">
            <i class="fas fa-check-circle"></i>
            <strong>Scan found in database</strong>
        </div>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">ID</div>
                <div class="info-value"><?php echo $scan['id']; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">User ID</div>
                <div class="info-value"><?php echo $scan['user_id']; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Filename</div>
                <div class="info-value"><?php echo htmlspecialchars($scan['filename']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Original Filename</div>
                <div class="info-value"><?php echo htmlspecialchars($scan['original_filename']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">File Size</div>
                <div class="info-value">
                    <?php echo number_format($scan['file_size']); ?> bytes 
                    (<?php echo round($scan['file_size'] / 1024, 2); ?> KB)
                </div>
            </div>
            <div class="info-item">
                <div class="info-label">MIME Type</div>
                <div class="info-value"><?php echo htmlspecialchars($scan['mime_type']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Is Saved</div>
                <div class="info-value"><?php echo $scan['is_saved'] ? 'Yes ✅' : 'No ❌'; ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">Created At</div>
                <div class="info-value"><?php echo $scan['created_at']; ?></div>
            </div>
        </div>

        <h3 style="color: #1f2937; margin-top: 30px;">
            <i class="fas fa-database"></i> File Data Analysis
        </h3>

        <?php
        $fileData = $scan['file_data'];
        $dataLength = strlen($fileData);
        $firstBytes = substr($fileData, 0, 100);
        $hasPdfHeader = substr($fileData, 0, 4) === '%PDF';
        ?>

        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">Data Length</div>
                <div class="info-value"><?php echo $dataLength; ?> bytes</div>
            </div>
            <div class="info-item">
                <div class="info-label">PDF Header</div>
                <div class="info-value">
                    <?php if ($hasPdfHeader): ?>
                        ✅ Valid (%PDF)
                    <?php else: ?>
                        ❌ Invalid (First 4 bytes: <?php echo htmlspecialchars(substr($fileData, 0, 4)); ?>)
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <h3 style="color: #1f2937; margin-top: 30px;">
            <i class="fas fa-code"></i> First 500 Bytes (Hex)
        </h3>
        <div class="code-block"><?php echo bin2hex($firstBytes); ?></div>

        <h3 style="color: #1f2937; margin-top: 30px;">
            <i class="fas fa-file-alt"></i> First 500 Bytes (Raw)
        </h3>
        <div class="code-block"><?php echo htmlspecialchars($firstBytes); ?></div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
            <h3 style="color: #1f2937;">Test Actions</h3>
            <a href="/view-scan-saved/<?php echo $scan['id']; ?>" class="btn" target="_blank">
                <i class="fas fa-eye"></i> View PDF
            </a>
            <a href="/download-scan-saved/<?php echo $scan['id']; ?>" class="btn">
                <i class="fas fa-download"></i> Download PDF
            </a>
            <a href="/scan" class="btn" style="background: #64748b;">
                <i class="fas fa-arrow-left"></i> Back to Scans
            </a>
        </div>

        <?php if (!$hasPdfHeader): ?>
            <div class="status-error" style="margin-top: 20px;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Warning: This doesn't appear to be a valid PDF!</strong><br><br>
                The file data doesn't start with %PDF header. This could mean:
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>The PDF generation failed</li>
                    <li>The data was corrupted during save</li>
                    <li>The wrong data type was saved</li>
                </ul>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

</body>
</html>
