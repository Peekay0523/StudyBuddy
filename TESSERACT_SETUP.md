# Tesseract OCR Setup Guide for StudySmart

## Overview

StudySmart now supports **Tesseract OCR** for processing image-based PDFs (scanned documents). This allows you to generate memorandums from scanned study materials.

## Quick Start

### 1. Test Your Current Setup

Visit: `http://localhost:8000/test-tesseract.php`

This page will show you:
- ✓ If Tesseract is installed and accessible
- ✓ If ImageMagick is available for PDF conversion
- ✓ Step-by-step installation instructions if needed

### 2. Install Tesseract OCR (Windows)

#### Step 1: Download Tesseract

Download the installer from: **https://github.com/UB-Mannheim/tesseract/wiki**

Recommended file: `tesseract-ocr-w64-setup-5.x.x.exe` (64-bit Windows)

#### Step 2: Install Tesseract

1. Run the installer
2. Accept the license agreement
3. Choose installation directory (default: `C:\Program Files\Tesseract-OCR`)
4. **IMPORTANT:** Check "Additional language data" and select languages you need:
   - English (eng)
   - Any other languages you study with
5. Complete the installation

#### Step 3: Verify Installation

Open Command Prompt and run:
```bash
tesseract --version
```

You should see version information like:
```
tesseract 5.x.x
 leptonica-1.x.x
```

#### Step 4: Add to PATH (if needed)

If `tesseract --version` doesn't work:

1. Press `Win + R`, type `sysdm.cpl`, press Enter
2. Click **Advanced** tab → **Environment Variables**
3. Under **System variables**, find **Path** and click **Edit**
4. Click **New** and add: `C:\Program Files\Tesseract-OCR`
5. Click **OK** to save
6. Restart your terminal/web server

#### Step 5: Set TESSERACT_PATH (Optional)

In the Environment Variables window:

1. Under **System variables**, click **New**
2. Variable name: `TESSERACT_PATH`
3. Variable value: `C:\Program Files\Tesseract-OCR\tesseract.exe`
4. Click **OK** to save

### 3. Install ImageMagick (for PDF to Image conversion)

Tesseract needs ImageMagick to convert PDF pages to images.

#### Download and Install

1. Download from: **https://imagemagick.org/script/download.php#windows**
2. Run the installer
3. **IMPORTANT:** Check "Install legacy utilities (e.g. convert)"
4. Complete installation

#### Verify Installation

```bash
magick --version
```

### 4. Restart Your Web Server

After installing Tesseract and ImageMagick:

**PHP Built-in Server:**
```bash
# Stop current server (Ctrl+C)
php -S localhost:8000 -t public
```

**Apache:**
```bash
# Restart Apache service
httpd -k restart
```

### 5. Test OCR Functionality

1. Visit: `http://localhost:8000/test-tesseract.php`
2. You should see: **✓ Your system is ready for OCR!**

### 6. Use OCR with Your PDFs

1. Upload an image-based PDF through the normal upload interface
2. Click **Generate Memorandum**
3. The system will automatically:
   - Detect if the PDF is image-based
   - Convert PDF pages to images
   - Use Tesseract to extract text
   - Generate the memorandum from extracted text

## How It Works

### OCR Processing Flow

```
Upload Image-Based PDF
         ↓
Detect binary/image content
         ↓
Convert PDF pages to images (ImageMagick)
         ↓
Extract text from images (Tesseract OCR)
         ↓
Generate memorandum (AI)
```

### Priority Order

The system tries OCR methods in this order:

1. **Tesseract OCR** (free, local, fast)
2. **Imagick + OpenAI Vision** (requires API key)
3. **Error message** (if no OCR method available)

## Troubleshooting

### "Tesseract is NOT available"

**Problem:** Tesseract not found in PATH

**Solution:**
1. Verify installation: `C:\Program Files\Tesseract-OCR\tesseract.exe` exists
2. Add to PATH (see Step 4 above)
3. Restart web server
4. Run `tesseract --version` in terminal to confirm

### "ImageMagick CLI is NOT available"

**Problem:** Can't convert PDF to images

**Solution:**
1. Install ImageMagick (see Step 3 above)
2. During installation, check "Install legacy utilities"
3. Verify: `magick --version`
4. Restart web server

### OCR runs but extracts garbage text

**Possible causes:**
- Poor scan quality (try higher DPI scans)
- Handwritten text (Tesseract works best with printed text)
- Non-English text without language pack

**Solutions:**
1. Install additional language packs during Tesseract installation
2. Ensure PDF is clear and high contrast
3. Try re-scanning at higher resolution (300 DPI recommended)

### "No OCR method available"

**Problem:** Neither Tesseract nor Imagick+OpenAI available

**Solutions:**
1. Install Tesseract (primary solution)
2. Or configure OpenAI API key for Vision API fallback
3. Upload text-based PDFs instead of scanned images

## Configuration

### Change Tesseract Path

If installed in a custom location, set environment variable:

**Windows (System Properties → Environment Variables):**
```
TESSERACT_PATH = C:\Custom\Path\tesseract.exe
```

**Linux/Mac (.bashrc or .zshrc):**
```bash
export TESSERACT_PATH=/usr/bin/tesseract
```

### Change OCR Language

By default, English (`eng`) is used. To use other languages:

1. Install language packs during Tesseract installation
2. Modify `TesseractHelper.php`:
```php
// Line ~106
$textContent = $this->tesseractHelper->extractTextFromPdf($filePath, 'fra'); // French
```

Available language codes: `eng`, `fra`, `deu`, `spa`, `chi_sim`, `jpn`, etc.

## Performance Tips

### Processing Speed

- **First page only:** System processes first 3 pages max by default
- **Resolution:** 150 DPI is used (balance of speed vs accuracy)
- **Large PDFs:** Consider splitting into smaller files

### Accuracy Improvement

- Use high-quality scans (300 DPI recommended)
- Ensure good contrast (black text on white background)
- Avoid skewed or rotated pages
- Use printed text (handwriting recognition is less accurate)

## API Usage

### Using TesseractHelper Directly

```php
require_once 'helpers/TesseractHelper.php';

$tesseract = new TesseractHelper();

// Check availability
if ($tesseract->isAvailable()) {
    // Extract from image
    $text = $tesseract->extractTextFromImage('path/to/image.jpg');
    
    // Extract from PDF (auto-converts to images)
    $text = $tesseract->extractTextFromPdf('path/to/document.pdf');
}
```

## Security Notes

- Tesseract runs locally (no data sent to external services)
- Temporary files are stored in system temp directory
- Files are automatically cleaned up after processing
- No API keys or credentials required

## Support

If you encounter issues:

1. Run `/test-tesseract.php` for diagnostic information
2. Check error logs: `error_log()` output in server logs
3. Verify all installation steps completed successfully
4. Ensure web server has permission to execute Tesseract

## Alternatives

If Tesseract doesn't work for your use case:

1. **OpenAI Vision API:** Configure `OPENAI_API_KEY` in config
2. **Online OCR services:** Convert PDF to text externally, then upload
3. **Text-based PDFs:** Use native PDF exports instead of scans

---

**Last Updated:** March 2026  
**Version:** StudySmart OCR v1.0
