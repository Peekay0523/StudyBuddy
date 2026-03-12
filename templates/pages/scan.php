<?php
$pageTitle = 'Scan to PDF - StudySmart';
$currentPage = 'scan';

requireLogin();

$currentUser = getCurrentUser();
if (!$currentUser) {
    header('Location: /login');
    exit;
}

// Get scan limit info
$scanInfo = getScanLimitInfo($currentUser['id']);
$isFreeTier = $scanInfo['is_free_tier'];
$scanLimit = $scanInfo['limit'];
$scansUsed = $scanInfo['used'];
$scansRemaining = $scanInfo['remaining'];
$periodEnd = $scanInfo['period_end'];

include __DIR__ . '/../layouts/header.php';
?>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255, 255, 255, 0.9); display: flex; justify-content: center; align-items: center; z-index: 9999; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease;">
    <div class="loading-spinner" style="width: 60px; height: 60px; border: 4px solid #e9ecef; border-top: 4px solid #667eea; border-radius: 50%; animation: spin 1s linear infinite;"></div>
    <div class="loading-text" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, 50px); color: #6c757d; font-size: 1rem; font-weight: 500;">Converting to PDF...</div>
</div>
<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<h1 class="title">Scan to PDF</h1>
<p class="subtitle">Convert images to PDF documents instantly</p>

<?php if ($isFreeTier): ?>
<div class="scan-info-banner" style="max-width: 800px; margin: 20px auto; padding: 15px 20px; background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); border-radius: 8px; border-left: 4px solid #f59e0b;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-info-circle" style="color: #f59e0b; font-size: 20px;"></i>
            <div>
                <strong style="color: #92400e;">Free Plan Scan Limit</strong>
                <p style="margin: 5px 0 0 0; color: #78350f; font-size: 14px;">
                    You've used <strong><?php echo $scansUsed; ?> of <?php echo $scanLimit; ?></strong> scan(s) this period.
                    <?php if ($periodEnd): ?>
                        Resets on <?php echo date('M d, Y', strtotime($periodEnd)); ?>.
                    <?php endif; ?>
                </p>
            </div>
        </div>
        <a href="/subscription" class="btn-primary" style="padding: 8px 16px; font-size: 14px; text-decoration: none; white-space: nowrap;">
            <i class="fas fa-arrow-up"></i> Upgrade for Unlimited
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Points & Rewards Banner -->
<div class="points-banner" style="max-width: 800px; margin: 20px auto; padding: 20px; background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%); border-radius: 12px; border: 2px solid #3b82f6; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #fbbf24, #f59e0b); border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
                <i class="fas fa-coins" style="font-size: 28px; color: white;"></i>
            </div>
            <div>
                <h3 style="margin: 0 0 5px 0; color: #1e40af; font-size: 18px;">
                    <i class="fas fa-gift"></i> Your Rewards
                </h3>
                <div style="display: flex; gap: 20px; align-items: center;">
                    <p style="margin: 0; color: #1e3a8a; font-size: 14px;">
                        <strong style="color: #f59e0b; font-size: 18px;"><?php echo $scanInfo['points']; ?> Points</strong>
                        <span style="color: #64748b; font-size: 12px;">| Earn 100 points per friend who joins!</span>
                    </p>
                    <?php if ($scanInfo['free_scans'] > 0): ?>
                        <span style="background: linear-gradient(135deg, #10b981, #059669); color: white; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> <?php echo $scanInfo['free_scans']; ?> Free Scan(s) Available
                        </span>
                    <?php endif; ?>
                </div>
                <p style="margin: 5px 0 0 0; color: #3b82f6; font-size: 13px;">
                    <i class="fas fa-bolt"></i> 500 points = 1 Free Scan
                </p>
            </div>
        </div>
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="/dashboard" class="btn-secondary" style="padding: 10px 16px; font-size: 14px; text-decoration: none; border: 2px solid #3b82f6; color: #3b82f6; background: white;">
                <i class="fas fa-users"></i> Invite Friends
            </a>
            <?php if ($scanInfo['points'] >= 500): ?>
                <button onclick="convertPointsToScan()" class="btn-primary" style="padding: 10px 16px; font-size: 14px; background: linear-gradient(135deg, #10b981, #059669); border: none;">
                    <i class="fas fa-exchange-alt"></i> Convert 500 Points to Free Scan
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="scan-container" style="max-width: 800px; margin: 0 auto;">
    <!-- Upload Area -->
    <div class="upload-area" id="upload-area" style="border: 3px dashed #cbd5e1; border-radius: 12px; padding: 40px; text-align: center; background: #f8fafc; cursor: pointer; transition: all 0.3s ease;">
        <input type="file" id="image-input" accept="image/*" multiple style="display: none;">
        <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #667eea; margin-bottom: 20px;"></i>
        <h3 style="margin-bottom: 10px; color: #1e293b;">Drop images here or click to upload</h3>
        <p style="color: #64748b; font-size: 14px;">Supports: JPG, PNG, GIF, WEBP (Multiple images allowed)</p>
    </div>

    <!-- Image Preview Section -->
    <div id="preview-section" style="display: none; margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;"><i class="fas fa-images"></i> Selected Images (<span id="image-count">0</span>)</h3>
            <button id="clear-all-btn" class="btn-secondary" style="padding: 8px 16px; font-size: 14px;">
                <i class="fas fa-trash"></i> Clear All
            </button>
        </div>
        
        <div id="image-previews" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <!-- Image previews will be added here -->
        </div>

        <!-- Convert Buttons -->
        <div class="convert-buttons-container" style="text-align: center; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <button id="convert-btn" class="btn-primary" style="padding: 15px 40px; font-size: 16px;">
                <i class="fas fa-file-pdf"></i> Convert to PDF
            </button>
            <button id="convert-bw-btn" class="btn-primary" style="padding: 15px 40px; font-size: 16px; background: linear-gradient(135deg, #475569, #64748b);" title="Requires GD library">
                <i class="fas fa-file-pdf"></i> Convert to B&W PDF
            </button>
        </div>
        <p id="gd-warning" style="display: none; color: #dc2626; font-size: 14px; margin-top: 10px;">
            <i class="fas fa-exclamation-triangle"></i> GD library not available. B&W conversion disabled. Please enable GD in php.ini
        </p>
    </div>

    <!-- Processing Indicator -->
    <div id="processing-section" style="display: none; text-align: center; padding: 40px;">
        <i class="fas fa-spinner fa-spin" style="font-size: 48px; color: #667eea; margin-bottom: 20px;"></i>
        <h3>Converting to PDF...</h3>
        <p style="color: #64748b;">Please wait while we process your images</p>
    </div>

    <!-- Success Section -->
    <div id="success-section" style="display: none; text-align: center; padding: 40px; background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); border-radius: 12px; margin-top: 30px;">
        <i class="fas fa-check-circle" style="font-size: 48px; color: #059669; margin-bottom: 20px;"></i>
        <h3 style="color: #065f46;">PDF Created Successfully!</h3>
        <p style="color: #047857; margin-bottom: 20px;">Your PDF is ready for download</p>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a id="download-link" href="#" class="btn-primary" style="text-decoration: none;">
                <i class="fas fa-download"></i> Download PDF
            </a>
            <button id="save-here-btn" class="btn-primary" style="background: linear-gradient(135deg, #16a34a, #22c55e);">
                <i class="fas fa-save"></i> Save Here
            </button>
            <button id="convert-another-btn" class="btn-secondary">
                <i class="fas fa-redo"></i> Convert Another
            </button>
        </div>
    </div>
</div>

<!-- Saved PDFs Section -->
<div id="saved-pdfs-section" class="saved-pdfs-section" style="max-width: 800px; margin: 40px auto;">
    <h3 style="margin-bottom: 20px; color: #1f2937; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-folder-open" style="color: #667eea;"></i> My Saved PDFs
    </h3>
    <div id="saved-pdfs-list" class="saved-pdfs-list" style="display: grid; gap: 15px;">
        <!-- Saved PDFs will be loaded here -->
    </div>
    <div id="no-saved-pdfs" style="text-align: center; padding: 40px; color: #6b7280; display: none;">
        <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; display: block;"></i>
        <p>No saved PDFs yet. Convert and save your first PDF!</p>
    </div>
</div>

<!-- Save Modal -->
<div id="save-modal-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div id="save-modal" style="background: white; padding: 30px; border-radius: 12px; max-width: 400px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
        <h3 style="margin-bottom: 20px; color: #1f2937; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-save" style="color: #16a34a;"></i> Save PDF
        </h3>
        <div style="margin-bottom: 20px;">
            <label for="save-filename" style="display: block; margin-bottom: 8px; font-weight: 600; color: #374151;">Enter PDF filename:</label>
            <input type="text" id="save-filename" placeholder="my_scan_2026-03-04" style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 16px; box-sizing: border-box;">
            <small style="color: #6b7280; display: block; margin-top: 5px;">.pdf will be added automatically</small>
        </div>
        <div style="display: flex; gap: 10px; justify-content: flex-end;">
            <button id="save-modal-cancel" class="btn-secondary" style="padding: 10px 20px;">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button id="save-modal-confirm" class="btn-primary" style="padding: 10px 20px; background: linear-gradient(135deg, #16a34a, #22c55e);">
                <i class="fas fa-check"></i> Save
            </button>
        </div>
    </div>
</div>

<script>
const uploadArea = document.getElementById('upload-area');
const imageInput = document.getElementById('image-input');
const previewSection = document.getElementById('preview-section');
const imagePreviews = document.getElementById('image-previews');
const imageCount = document.getElementById('image-count');
const clearAllBtn = document.getElementById('clear-all-btn');
const convertBtn = document.getElementById('convert-btn');
const convertBwBtn = document.getElementById('convert-bw-btn');
const processingSection = document.getElementById('processing-section');
const successSection = document.getElementById('success-section');
const downloadLink = document.getElementById('download-link');
const convertAnotherBtn = document.getElementById('convert-another-btn');
const saveHereBtn = document.getElementById('save-here-btn');
const saveModalOverlay = document.getElementById('save-modal-overlay');
const saveModal = document.getElementById('save-modal');
const saveModalCancel = document.getElementById('save-modal-cancel');
const saveModalConfirm = document.getElementById('save-modal-confirm');
const saveFilenameInput = document.getElementById('save-filename');

let currentPdfUrl = '';
let currentScanId = null;

let selectedImages = [];

// Click to upload
uploadArea.addEventListener('click', () => {
    imageInput.click();
});

// Drag and drop
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = '#667eea';
    uploadArea.style.background = '#e0e7ff';
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.style.borderColor = '#cbd5e1';
    uploadArea.style.background = '#f8fafc';
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.style.borderColor = '#cbd5e1';
    uploadArea.style.background = '#f8fafc';
    
    const files = Array.from(e.dataTransfer.files).filter(file => file.type.startsWith('image/'));
    handleFiles(files);
});

// File input change
imageInput.addEventListener('change', (e) => {
    const files = Array.from(e.target.files).filter(file => file.type.startsWith('image/'));
    handleFiles(files);
    imageInput.value = ''; // Reset to allow same file selection
});

function handleFiles(files) {
    if (files.length === 0) return;
    
    selectedImages = [...selectedImages, ...files];
    updatePreviews();
}

function updatePreviews() {
    imagePreviews.innerHTML = '';
    imageCount.textContent = selectedImages.length;
    
    if (selectedImages.length > 0) {
        previewSection.style.display = 'block';
        uploadArea.style.display = 'none';
    }
    
    selectedImages.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const div = document.createElement('div');
            div.className = 'image-preview-card';
            div.style.cssText = 'position: relative; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; background: white;';
            div.innerHTML = `
                <img src="${e.target.result}" style="width: 100%; height: 150px; object-fit: cover;">
                <button class="remove-image-btn" data-index="${index}" 
                        style="position: absolute; top: 8px; right: 8px; background: rgba(239, 68, 68, 0.9); color: white; border: none; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-size: 14px;">
                    <i class="fas fa-times"></i>
                </button>
                <div style="padding: 8px; font-size: 12px; color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    ${file.name}
                </div>
            `;
            imagePreviews.appendChild(div);
            
            // Add remove button event
            div.querySelector('.remove-image-btn').addEventListener('click', (e) => {
                e.stopPropagation();
                removeImage(index);
            });
        };
        reader.readAsDataURL(file);
    });
}

function removeImage(index) {
    selectedImages.splice(index, 1);
    updatePreviews();
    
    if (selectedImages.length === 0) {
        previewSection.style.display = 'none';
        uploadArea.style.display = 'block';
    }
}

// Clear all
clearAllBtn.addEventListener('click', () => {
    selectedImages = [];
    previewSection.style.display = 'none';
    uploadArea.style.display = 'block';
    imagePreviews.innerHTML = '';
});

// Convert to PDF
convertBtn.addEventListener('click', async () => {
    if (selectedImages.length === 0) {
        alert('Please select at least one image');
        return;
    }

    // Check scan limit before proceeding
    <?php if ($isFreeTier && $scansRemaining <= 0): ?>
    alert('You have reached your free tier scan limit for this period. Please upgrade to Basic or Premium for unlimited scans.');
    window.location.href = '/subscription';
    return;
    <?php elseif ($isFreeTier && $scansRemaining === 1): ?>
    if (!confirm('Warning: This will use your last free scan for this period. Continue?')) {
        return;
    }
    <?php endif; ?>

    previewSection.style.display = 'none';
    processingSection.style.display = 'block';
    
    // Show loading overlay
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingText = loadingOverlay.querySelector('.loading-text');
    if (loadingText) loadingText.textContent = 'Converting to PDF...';
    if (loadingOverlay) loadingOverlay.classList.add('active');

    const formData = new FormData();
    selectedImages.forEach((file, index) => {
        formData.append('images[]', file);
    });

    try {
        const response = await fetch('/api/scan-to-pdf', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin' // Ensure cookies are sent
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);

        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);

        // Debug: log raw response text first
        const rawText = await response.text();
        console.log('Raw response text:', rawText);

        if (!contentType || !contentType.includes('application/json')) {
            console.error('Non-JSON response:', rawText);
            throw new Error('Server returned non-JSON response: ' + rawText.substring(0, 200));
        }

        let data;
        try {
            data = JSON.parse(rawText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Failed to parse:', rawText);
            throw new Error('Failed to parse JSON response: ' + parseError.message);
        }

        console.log('Parsed response data:', data);
        console.log('data.scan_id type:', typeof data.scan_id);
        console.log('data.scan_id value:', data.scan_id);

        if (data.success) {
            console.log('Conversion successful!');
            processingSection.style.display = 'none';
            successSection.style.display = 'block';
            downloadLink.href = data.download_url;
            currentPdfUrl = data.download_url;
            currentScanId = data.scan_id;
            console.log('currentScanId is now:', currentScanId);
            console.log('currentScanId truthy?:', !!currentScanId);
            
            // Hide loading overlay
            if (loadingOverlay) loadingOverlay.classList.remove('active');
        } else {
            console.error('API returned success=false:', data.error);
            throw new Error(data.error || 'Conversion failed');
        }
    } catch (error) {
        console.error('PDF conversion error:', error);
        alert('Error converting to PDF: ' + error.message);
        processingSection.style.display = 'none';
        previewSection.style.display = 'block';
        
        // Hide loading overlay on error
        if (loadingOverlay) loadingOverlay.classList.remove('active');
    }
});

// Convert to Black & White PDF
convertBwBtn.addEventListener('click', async () => {
    if (selectedImages.length === 0) {
        alert('Please select at least one image');
        return;
    }

    // Check scan limit before proceeding
    <?php if ($isFreeTier && $scansRemaining <= 0): ?>
    alert('You have reached your free tier scan limit for this period. Please upgrade to Basic or Premium for unlimited scans.');
    window.location.href = '/subscription';
    return;
    <?php elseif ($isFreeTier && $scansRemaining === 1): ?>
    if (!confirm('Warning: This will use your last free scan for this period. Continue?')) {
        return;
    }
    <?php endif; ?>

    previewSection.style.display = 'none';
    processingSection.style.display = 'block';
    
    // Show loading overlay
    const loadingOverlay = document.getElementById('loadingOverlay');
    const loadingText = loadingOverlay.querySelector('.loading-text');
    if (loadingText) loadingText.textContent = 'Converting to B&W PDF...';
    if (loadingOverlay) loadingOverlay.classList.add('active');

    const formData = new FormData();
    selectedImages.forEach((file, index) => {
        formData.append('images[]', file);
    });
    formData.append('black_white', 'true');

    try {
        const response = await fetch('/api/scan-to-pdf', {
            method: 'POST',
            body: formData
        });

        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error('Server returned non-JSON response: ' + text.substring(0, 200));
        }

        const data = await response.json();

        console.log('B&W conversion response:', data);
        console.log('data.scan_id:', data.scan_id);

        if (data.success) {
            processingSection.style.display = 'none';
            successSection.style.display = 'block';
            downloadLink.href = data.download_url;
            currentPdfUrl = data.download_url;
            currentScanId = data.scan_id;
            console.log('currentScanId set to:', currentScanId);
            
            // Hide loading overlay
            if (loadingOverlay) loadingOverlay.classList.remove('active');
        } else {
            throw new Error(data.error || 'Conversion failed');
        }
    } catch (error) {
        console.error('PDF conversion error:', error);
        alert('Error converting to PDF: ' + error.message);
        processingSection.style.display = 'none';
        previewSection.style.display = 'block';
        
        // Hide loading overlay on error
        if (loadingOverlay) loadingOverlay.classList.remove('active');
    }
});

// Convert another
convertAnotherBtn.addEventListener('click', () => {
    selectedImages = [];
    successSection.style.display = 'none';
    uploadArea.style.display = 'block';
    previewSection.style.display = 'none';
    imagePreviews.innerHTML = '';
});

// Save Here - show modal to prompt for filename and save
saveHereBtn.addEventListener('click', () => {
    if (!currentScanId) {
        alert('No scan ID available. Please try converting the images to PDF again.');
        return;
    }
    
    const defaultName = 'my_scan_' + new Date().toISOString().slice(0,10);
    saveFilenameInput.value = defaultName;
    saveModalOverlay.style.display = 'flex';
    saveFilenameInput.focus();
    saveFilenameInput.select();
});

// Close modal
saveModalCancel.addEventListener('click', () => {
    saveModalOverlay.style.display = 'none';
});

saveModalOverlay.addEventListener('click', (e) => {
    if (e.target === saveModalOverlay) {
        saveModalOverlay.style.display = 'none';
    }
});

// Confirm save
saveModalConfirm.addEventListener('click', async () => {
    const filename = saveFilenameInput.value.trim();

    if (!filename) {
        alert('Please enter a filename');
        return;
    }
    
    if (!currentScanId) {
        alert('No scan ID available. Please try converting the images to PDF again.');
        return;
    }

    const cleanFilename = filename.replace(/[^a-zA-Z0-9_-]/g, '_') + '.pdf';

    try {
        const response = await fetch('/api/scan-save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                scan_id: currentScanId,
                filename: cleanFilename
            })
        });

        const data = await response.json();

        if (data.success) {
            alert('PDF saved successfully as: ' + data.filename);
            saveModalOverlay.style.display = 'none';
            loadSavedPdfs(); // Reload saved list
        } else {
            throw new Error(data.error || 'Failed to save PDF');
        }
    } catch (error) {
        console.error('Save error:', error);
        alert('Error saving PDF: ' + error.message);
    }
});

// Handle Enter key in input
saveFilenameInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        saveModalConfirm.click();
    }
});

// Load saved PDFs on page load
document.addEventListener('DOMContentLoaded', loadSavedPdfs);

// Check GD availability
checkGDAvailability();

async function checkGDAvailability() {
    try {
        const response = await fetch('/test-gd');
        const html = await response.text();
        if (html.includes('✗ GD is NOT available')) {
            document.getElementById('gd-warning').style.display = 'block';
            document.getElementById('convert-bw-btn').disabled = true;
            document.getElementById('convert-bw-btn').style.opacity = '0.5';
            document.getElementById('convert-bw-btn').style.cursor = 'not-allowed';
        }
    } catch (error) {
        console.error('Error checking GD:', error);
    }
}

async function loadSavedPdfs() {
    try {
        const response = await fetch('/api/scan-saved-list');
        const data = await response.json();

        const savedPdfsList = document.getElementById('saved-pdfs-list');
        const noSavedPdfs = document.getElementById('no-saved-pdfs');

        if (data.success && data.files.length > 0) {
            noSavedPdfs.style.display = 'none';
            savedPdfsList.innerHTML = data.files.map(file => `
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px; background: white; border-radius: 8px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="display: flex; align-items: center; gap: 15px; flex: 1;">
                        <i class="fas fa-file-pdf" style="font-size: 32px; color: #dc2626;"></i>
                        <div>
                            <h4 style="margin: 0; color: #1f2937;">${escapeHtml(file.name)}</h4>
                            <small style="color: #6b7280;">${file.size} • ${file.date}</small>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="/view-scan-saved/${file.id}" target="_blank" class="btn-sm btn-sm-warning" style="text-decoration: none; padding: 8px 16px; border-radius: 6px; background: linear-gradient(135deg, #f59e0b, #f97316); color: white;">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="${file.url}" class="btn-sm btn-sm-primary" style="text-decoration: none; padding: 8px 16px; border-radius: 6px;">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button onclick="deleteSavedPdf(${file.id})" class="btn-sm btn-sm-danger" style="padding: 8px 16px; border-radius: 6px; border: none; background: #dc2626; color: white; cursor: pointer;">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            noSavedPdfs.style.display = 'block';
            savedPdfsList.innerHTML = '';
        }
    } catch (error) {
        console.error('Error loading saved PDFs:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Delete saved PDF
async function deleteSavedPdf(id) {
    if (!confirm('Are you sure you want to delete this scan?')) {
        return;
    }

    try {
        const response = await fetch('/api/scan-delete-saved', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        });

        const data = await response.json();

        if (data.success) {
            loadSavedPdfs(); // Reload the list
        } else {
            alert('Error deleting PDF: ' + data.error);
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Error deleting PDF: ' + error.message);
    }
}
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>

<style>
/* Scan Page Mobile Responsiveness */
@media (max-width: 768px) {
    .scan-container,
    #saved-pdfs-section {
        padding: 0 15px;
    }

    .upload-area {
        padding: 20px 15px !important;
    }

    .upload-area i {
        font-size: 36px !important;
    }

    .upload-area h3 {
        font-size: 16px !important;
    }

    .upload-area p {
        font-size: 13px !important;
    }

    #image-previews {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)) !important;
        gap: 10px !important;
    }

    #image-previews img {
        height: 100px !important;
    }

    .convert-buttons-container {
        flex-direction: column !important;
        width: 100%;
    }

    #convert-btn,
    #convert-bw-btn {
        width: 100% !important;
        padding: 12px 20px !important;
        font-size: 14px !important;
    }

    #success-section {
        padding: 20px 15px !important;
    }

    #success-section i {
        font-size: 36px !important;
    }

    #success-section h3 {
        font-size: 18px !important;
    }

    #success-section p {
        font-size: 14px !important;
    }

    #success-section .btn-primary,
    #success-section .btn-secondary {
        width: 100%;
        margin-bottom: 10px;
    }

    /* Saved PDFs Mobile */
    .saved-pdfs-list > div {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 15px !important;
        padding: 15px !important;
    }

    .saved-pdfs-list .btn-sm {
        width: 100%;
        text-align: center;
        box-sizing: border-box;
        display: block !important;
    }

    .saved-pdfs-list > div > div:last-child {
        width: 100%;
        display: flex !important;
        flex-direction: column;
        gap: 8px;
    }

    /* Modal Mobile */
    #save-modal {
        max-width: 95% !important;
        padding: 20px 15px !important;
    }

    #save-filename {
        font-size: 14px !important;
        padding: 10px !important;
    }

    #save-modal .btn-secondary,
    #save-modal .btn-primary {
        padding: 10px 15px !important;
        font-size: 14px !important;
    }

    /* Info Banner Mobile */
    .scan-info-banner {
        flex-direction: column !important;
        text-align: center;
    }

    .scan-info-banner > div {
        justify-content: center !important;
    }

    .scan-info-banner .btn-primary {
        width: 100%;
        text-align: center;
    }
}

@media (max-width: 480px) {
    .upload-area {
        padding: 15px 10px !important;
    }

    .upload-area i {
        font-size: 28px !important;
    }

    .upload-area h3 {
        font-size: 14px !important;
    }

    #image-previews {
        grid-template-columns: repeat(2, 1fr) !important;
    }

    .title {
        font-size: 24px !important;
    }

    .subtitle {
        font-size: 14px !important;
    }
}
</style>

<!-- Convert Points Modal -->
<div id="convertPointsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
    <div style="background: white; padding: 30px; border-radius: 12px; width: 90%; max-width: 400px; position: relative; text-align: center;">
        <button onclick="document.getElementById('convertPointsModal').style.display='none'"
                style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #64748b;">
            &times;
        </button>

        <div style="margin-bottom: 20px;">
            <i class="fas fa-coins" style="font-size: 48px; color: #fbbf24; margin-bottom: 15px;"></i>
            <h2 style="margin: 0 0 10px 0; color: #1e293b;">Convert Points to Free Scan</h2>
            <p style="color: #64748b; font-size: 14px;">Exchange 500 points for 1 free scan</p>
        </div>

        <div style="background: #f0f9ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #3b82f6;">
            <p style="margin: 0; color: #0369a1; font-size: 14px;">
                <i class="fas fa-info-circle"></i> This will deduct 500 points from your balance and add 1 free scan to your account.
            </p>
        </div>

        <div style="display: flex; gap: 10px; justify-content: center;">
            <button type="button" onclick="document.getElementById('convertPointsModal').style.display='none'"
                    style="padding: 10px 20px; background: #f1f5f9; color: #64748b; border: none; border-radius: 6px; cursor: pointer; font-weight: 500;">
                Cancel
            </button>
            <form method="POST" action="/scan/convert-points" style="display: inline;">
                <button type="submit" class="btn-primary" style="background: linear-gradient(135deg, #10b981, #059669); border: none;">
                    <i class="fas fa-check"></i> Confirm Conversion
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function convertPointsToScan() {
    document.getElementById('convertPointsModal').style.display = 'flex';
}

// Close modal when clicking outside
window.onclick = function(event) {
    var modal = document.getElementById('convertPointsModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>
