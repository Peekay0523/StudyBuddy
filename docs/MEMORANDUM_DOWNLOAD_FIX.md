# Memorandum Download Fix & AI Generation Improvement

## Problem 1: PDF/DOCX Downloads Not Working
The PDF and DOCX memorandum downloads were not showing content properly because:
1. The `getMemorandumHtml()` method was using `htmlspecialchars()` on the entire content, which escaped all HTML
2. The fallback PDF generation was outputting HTML with PDF headers (causing "Failed to load PDF" error)
3. The fallback DOCX was missing proper Word XML headers

## Solution 1: Fixed Downloads

### Changes Made

#### 1. Fixed `getMemorandumHtml()` Method
**File:** `controllers/ScriptController.php`

The method now:
- Properly formats content by parsing newlines and converting them to paragraphs
- Handles markdown-style headings (`# ` and `## `)
- Converts bullet points (`- ` or `* `) to proper HTML lists
- Includes optional script metadata (subject, grade level)
- Applies `htmlspecialchars()` only to individual text elements, not the entire HTML

#### 2. Updated PDF Download Method
**File:** `controllers/ScriptController.php` - `downloadMemorandumAsPdf()`

- Now fetches script data to include in the document
- Passes script data to `getMemorandumHtml()`
- **Fallback mode**: Downloads as `.html` file (opens in browser, can be printed to PDF)

#### 3. Updated DOCX Download Method
**File:** `controllers/ScriptController.php` - `downloadMemorandumAsDocx()`

- Now fetches script data to include in the document
- When PHPWord is available, properly formats content with:
  - Title and metadata
  - Headings (bold, larger font)
  - Bullet points as proper lists
  - Paragraphs with proper spacing
- Fallback mode creates clean HTML document that opens in Word

#### 4. Added Output Buffering
**File:** `controllers/ScriptController.php` - `downloadMemorandum()`

- Added output buffering to prevent whitespace issues
- Added debug logging for troubleshooting

#### 5. Updated UI
**File:** `templates/pages/view_memorandum.php`

- Added info note in download modal explaining PDF downloads as HTML
- Changed "DOCX Format" to "Word Format" for clarity

#### 6. Updated composer.json
Added dependencies for proper document generation:
```json
"dompdf/dompdf": "^2.0",
"phpoffice/phpword": "^1.1"
```

---

## Problem 2: AI Memorandum Generation Format
The AI was generating memorandums without proper question-answer format, step-by-step solutions, or diagrams.

## Solution 2: Improved AI Prompt

### Changes Made

#### 1. Updated AIHelper.php - generateMemorandum()
**File:** `helpers/AIHelper.php`

The new system prompt instructs the AI to:

1. **QUESTION-ANSWER FORMAT**: 
   - Clearly state question number and full question text
   - Provide answer below it
   - Show step-by-step solution

2. **STRUCTURE FOR EACH QUESTION**:
   ```
   Question [number]: [Full question text]
   
   Answer: [Final answer]
   
   Solution/Explanation:
   - Step 1
   - Step 2
   - Step 3
   - Final result
   ```

3. **FOR MATHEMATICAL PROBLEMS**:
   - Show ALL calculation steps
   - Explain the formula or method used
   - Include units where applicable
   - Highlight the final answer

4. **FOR THEORY QUESTIONS**:
   - Clear, concise explanations
   - Bullet points for key points
   - Examples where helpful
   - Define technical terms

5. **FOR DIAGRAMS/DRAWINGS**:
   - Create ASCII art representations
   - Label all parts clearly
   - Explain what each part represents
   
   Example:
   ```
   ┌─────────────┐
   │   Nucleus   │
   │  (control   │
   │   center)   │
   └─────────────┘
   ```

6. **FORMATTING**:
   - No markdown (no **, ##, etc.)
   - Plain text with clear spacing
   - Simple characters for structure (---, |, etc.)
   - Clean and readable

7. **TONE**:
   - Educational and encouraging
   - Clear and easy to understand
   - Suitable for high school students

#### 2. Updated SEOController.php Prompts
**File:** `controllers/SEOController.php`

Updated both system and user prompts to follow the same question-answer-solution format.

---

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

### For Downloads:
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

### For AI Generation:
1. Upload a new script at: http://localhost:8000/upload-script
2. Wait for AI to generate memorandum
3. View the memorandum - it should now show:
   - Question numbers with full question text
   - Final answers
   - Step-by-step solutions
   - ASCII art diagrams where applicable

## File Changes Summary

| File | Changes |
|------|---------|
| `controllers/ScriptController.php` | Fixed `getMemorandumHtml()`, `downloadMemorandumAsPdf()`, `downloadMemorandumAsDocx()` methods, added output buffering |
| `helpers/AIHelper.php` | Updated `generateMemorandum()` with new detailed prompt for question-answer format |
| `controllers/SEOController.php` | Updated system and user prompts for consistent formatting |
| `composer.json` | Added dompdf/dompdf and phpoffice/phpword dependencies |
| `public/css/style.css` | Added modal and button styling |
| `templates/pages/view_memorandum.php` | Cleaned up inline styles, added format info note |
| `public/index.php` | Added test download route for debugging |

## Notes

- The fallback PDF downloads as HTML to avoid "Failed to load PDF" errors
- Users can easily convert HTML to PDF using browser's Print → Save as PDF feature
- The fallback DOCX includes proper Word XML namespace declarations for compatibility
- For production use, installing the proper libraries (Dompdf, PHPWord) is strongly recommended
- The new AI prompt requires more tokens (increased to 700) for detailed solutions

---

## OpenAI Token Usage Tracking

### New Feature: Admin Dashboard Token Tracking

A new database table `openai_usage_logs` tracks all OpenAI API token usage.

#### Setup Instructions:
1. Visit: http://localhost:8000/add_openai_usage_table.php
2. This creates the `openai_usage_logs` table automatically

#### What Gets Tracked:
- **Prompt tokens**: Tokens sent to OpenAI (input)
- **Completion tokens**: Tokens generated by OpenAI (output)
- **Total tokens**: Sum of prompt + completion tokens
- **User ID**: Which user triggered the API call
- **Timestamp**: When the API call was made

#### Admin Dashboard Shows:
- **Total Tokens Used**: All-time token consumption
- **Tokens This Month**: Current month's usage
- **Total API Calls**: Number of times AI was used
- **API Calls This Month**: Current month's API calls
- **Estimated Cost**: Based on GPT-4o-mini pricing ($0.0000006 per token)

#### Files Modified for Token Tracking:
| File | Changes |
|------|---------|
| `helpers/AIHelper.php` | Added `logTokenUsage()` method to track usage |
| `controllers/AdminController.php` | Added usage statistics to dashboard |
| `templates/pages/admin/dashboard.php` | Added 3 new stat cards for OpenAI usage |
| `add_openai_usage_table.php` | Script to create database table |
