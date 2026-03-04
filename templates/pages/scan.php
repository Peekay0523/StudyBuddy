<?php
$pageTitle = 'Scan to PDF - StudySmart';
$currentPage = 'scan';
include __DIR__ . '/../layouts/header.php';
?>

<h1 class="title">Scan to PDF</h1>
<p class="subtitle">Convert images to PDF documents instantly</p>

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

        <!-- Convert Button -->
        <div style="text-align: center;">
            <button id="convert-btn" class="btn-primary" style="padding: 15px 40px; font-size: 16px;">
                <i class="fas fa-file-pdf"></i> Convert to PDF
            </button>
        </div>
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
            <button id="convert-another-btn" class="btn-secondary">
                <i class="fas fa-redo"></i> Convert Another
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
const processingSection = document.getElementById('processing-section');
const successSection = document.getElementById('success-section');
const downloadLink = document.getElementById('download-link');
const convertAnotherBtn = document.getElementById('convert-another-btn');

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

    previewSection.style.display = 'none';
    processingSection.style.display = 'block';

    const formData = new FormData();
    selectedImages.forEach((file, index) => {
        formData.append('images[]', file);
    });

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

        if (data.success) {
            processingSection.style.display = 'none';
            successSection.style.display = 'block';
            downloadLink.href = data.download_url;
        } else {
            throw new Error(data.error || 'Conversion failed');
        }
    } catch (error) {
        console.error('PDF conversion error:', error);
        alert('Error converting to PDF: ' + error.message);
        processingSection.style.display = 'none';
        previewSection.style.display = 'block';
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
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
