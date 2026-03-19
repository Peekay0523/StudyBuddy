# Memorandum Download Fix

## Problem
The PDF and DOCX memorandum downloads were not showing content properly because:
1. The `getMemorandumHtml()` method was using `htmlspecialchars()` on the entire content, which escaped all HTML
2. The fallback PDF generation was outputting HTML with PDF headers (causing "Failed to load PDF" error)
3. The fallback DOCX was missing proper Word XML headers

## Changes Made

### 1. Fixed `getMemorandumHtml()` Method
**File:** `controllers/ScriptController.php`

The method now:
- Properly formats content by parsing newlines and converting them to paragraphs
- Handles markdown-style headings (`# ` and `## `)
- Converts bullet points (`- ` or `* `) to proper HTML lists
- Includes optional script metadata (subject, grade level)
- Applies `htmlspecialchars()` only to individual text elements, not the entire HTML

### 2. Updated PDF Download Method
**File:** `controllers/ScriptController.php` - `downloadMemorandumAsPdf()`

- Now fetches script data to include in the document
- Passes script data to `getMemorandumHtml()`
- **Fallback mode**: Downloads as `.html` file (opens in browser, can be printed to PDF)

### 3. Updated DOCX Download Method
**File:** `controllers/ScriptController.php` - `downloadMemorandumAsDocx()`

- Now fetches script data to include in the document
- When PHPWord is available, properly formats content with:
  - Title and metadata
  - Headings (bold, larger font)
  - Bullet points as proper lists
  - Paragraphs with proper spacing
- Fallback mode includes proper Word XML headers for better compatibility

### 4. Updated UI
**File:** `templates/pages/view_memorandum.php`

- Added info note in download modal explaining PDF downloads as HTML
- Changed "DOCX Format" to "Word Format" for clarity

### 5. Updated composer.json
Added dependencies for proper document generation:
```json
"dompdf/dompdf": "^2.0",
"phpoffice/phpword": "^1.1"
```

## How to Enable Better PDF/DOCX Support

Since Composer is not currently installed on your system, you have two options:

### Option 1: Install Composer (Recommended)
1. Download Composer from https://getcomposer.org/download/
2. Run: `composer install`
3. This will install Dompdf and PHPWord for professional PDF/DOCX generation

### Option 2: Use Current Fallback (Works Now)
The current fallback will:
- **PDF**: Download as `.html` file - open in any browser and use Print → Save as PDF
- **DOCX**: Download as HTML-based Word document that opens in Microsoft Word or LibreOffice

## Testing the Fix

1. Navigate to: http://localhost:8000/view-memorandum/19
2. Click the "Download" button
3. Choose PDF or Word format
4. The downloaded file should now contain:
   - Memorandum title
   - Subject and grade level information
   - Properly formatted content with paragraphs and bullet points

### For PDF:
- File will download as `.html`
- Open in browser (double-click)
- Press Ctrl+P (Print) → Choose "Save as PDF" as printer

### For Word:
- File will download as `.doc`
- Open in Microsoft Word, LibreOffice Writer, or Google Docs
- Content will be properly formatted

## File Changes Summary

| File | Changes |
|------|---------|
| `controllers/ScriptController.php` | Fixed `getMemorandumHtml()`, `downloadMemorandumAsPdf()`, `downloadMemorandumAsDocx()` methods |
| `composer.json` | Added dompdf/dompdf and phpoffice/phpword dependencies |
| `public/css/style.css` | Added modal and button styling |
| `templates/pages/view_memorandum.php` | Cleaned up inline styles, added format info note |

## Notes

- The fallback PDF downloads as HTML to avoid "Failed to load PDF" errors
- Users can easily convert HTML to PDF using browser's Print → Save as PDF feature
- The fallback DOCX includes proper Word XML namespace declarations for compatibility
- For production use, installing the proper libraries (Dompdf, PHPWord) is strongly recommended
