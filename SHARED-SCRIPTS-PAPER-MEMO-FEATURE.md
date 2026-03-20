# Shared Scripts with Paper Type and Memorandum Support

## Overview
Enhanced the shared scripts feature to include:
- **Paper Type Selection** (Paper 1, Paper 2, Paper 3)
- **Memorandum Upload** (optional memorandum file for each script)
- **Improved Admin UI** with better styling and organization

## Features

### For Admins
1. **Upload Shared Scripts with Paper Type**
   - Select which exam paper the script is for (Paper 1, 2, or 3)
   - Upload memorandum file (optional)
   - Beautiful form with sections and icons
   - File size validation (max 10MB for both files)

2. **Manage Shared Scripts**
   - View paper type in the table
   - See memorandum status at a glance
   - Download memorandum directly from admin panel
   - Delete scripts with confirmation

### For Students
1. **Browse Scripts by Paper Type**
   - See which paper each script is for
   - Identify scripts with memorandums via badge
   - Download memorandum separately if available

2. **Enhanced Script Cards**
   - Subject badge (blue)
   - Grade badge (yellow)
   - Paper badge (pink)
   - Memo Included badge (green) if available

## Database Changes

### New Columns Added to `uploaded_scripts` Table:
```sql
paper INTEGER DEFAULT NULL
memorandum_file_path VARCHAR(255) DEFAULT NULL
```

### Indexes Created:
- `idx_paper` - For filtering by paper type
- `idx_browse_paper` - Combined index for efficient browsing

## Setup Instructions

### 1. Run the Database Migration

```bash
php migrate_add_paper_and_memorandum_to_scripts.php
```

This will:
- Add `paper` column for exam paper identification
- Add `memorandum_file_path` column for memorandum storage
- Create necessary indexes for performance

### 2. Upload Shared Scripts

1. Login as admin
2. Go to `/admin/scripts`
3. Fill in the upload form:
   - **Title**: e.g., "Mathematics November 2025"
   - **Subject**: e.g., "Mathematics"
   - **Grade Level**: 8-12
   - **Paper Type**: Paper 1, 2, or 3
   - **Script File**: Upload the question paper (PDF, DOCX, TXT)
   - **Memorandum File**: Upload the memo (optional)
4. Click "Upload Shared Script"

### 3. Students Browse Scripts

1. Students go to `/upload-script`
2. Click on their grade from the sidebar
3. View available scripts with paper type badges
4. Download scripts and memorandums

## File Structure

```
SchoolApp/
├── controllers/
│   └── AdminController.php          # Updated uploadSharedScript() method
├── templates/
│   ├── pages/
│   │   ├── admin/
│   │   │   └── scripts.php          # Enhanced upload form & table
│   │   └── browse_scripts.php        # Updated student view with paper badges
│   └── layouts/
└── migrate_add_paper_and_memorandum_to_scripts.php
```

## UI Improvements

### Upload Form
- **Two sections**: Script Information & File Uploads
- **Icons** for all labels
- **Required field indicators** (red asterisks)
- **Focus states** with colored borders
- **Reset button** to clear form
- **File size validation** with user-friendly alerts
- **Responsive grid layout**

### Admin Table
- **New columns**: Paper, Memorandum
- **Paper badge** with yellow/pink color scheme
- **Memorandum status** with check/cross icons
- **Memo download button** (green) when memorandum exists
- **Better spacing** and visual hierarchy

### Student View
- **Four badge types** for better organization
- **Memo download button** appears when available
- **Color-coded badges**:
  - Subject: Blue
  - Grade: Yellow
  - Paper: Pink
  - Memo: Green

## Form Fields

### Script Information
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| Title | Text | Yes | Name of the script |
| Subject | Text | Yes | Subject name |
| Grade Level | Select | Yes | Grade 8-12 |
| Paper Type | Select | Yes | Paper 1, 2, or 3 |

### File Uploads
| Field | Type | Required | Max Size | Formats |
|-------|------|----------|----------|---------|
| Script File | File | Yes | 10MB | PDF, DOCX, TXT |
| Memorandum | File | No | 10MB | PDF, DOCX, TXT |

## Validation

### Client-Side (JavaScript)
```javascript
function validateFile(input, maxSizeMB) {
    const file = input.files[0];
    if (file) {
        const sizeMB = file.size / (1024 * 1024);
        if (sizeMB > maxSizeMB) {
            alert('File size must be less than ' + maxSizeMB + 'MB.');
            input.value = '';
        }
    }
}
```

### Server-Side (PHP)
- File type validation (PDF, DOCX, TXT only)
- File size validation (max 10MB)
- Paper type validation (1, 2, or 3 only)
- Required field validation

## File Naming Convention

### Scripts
```
shared_script_{timestamp}_{original_filename}
Example: shared_script_1711234567_mathematics_nov_2025.pdf
```

### Memorandums
```
shared_memo_{timestamp}_{original_filename}
Example: shared_memo_1711234567_mathematics_memo_nov_2025.pdf
```

## Routes

### Admin Routes
```
POST /admin/scripts/upload-shared
GET  /admin/scripts
```

### Student Routes
```
GET /browse-scripts/{grade}
GET /view-script/{id}
GET /download-script/{id}
GET /download-memorandum/{id}
```

## Security

- **Admin-only access**: Only admins can upload shared scripts
- **File type validation**: Prevents malicious file uploads
- **File size limits**: Prevents storage abuse (10MB max)
- **Student isolation**: Students can only view/download, not upload

## Future Enhancements

Potential improvements:
- [ ] Add paper type filtering on browse page
- [ ] Add memorandum preview (view online)
- [ ] Batch upload multiple papers at once
- [ ] Add script versioning (v1, v2, etc.)
- [ ] Add download statistics
- [ ] Add user ratings for scripts
- [ ] Add comments/discussion per script
- [ ] Add tags for better organization
- [ ] Add search functionality
- [ ] Add script thumbnail previews

## Troubleshooting

### Upload fails
1. Check file size (max 10MB)
2. Verify file type (PDF, DOCX, TXT only)
3. Check `UPLOAD_DIR_SCRIPTS` permissions
4. Review PHP error logs

### Memorandum not showing
1. Verify `memorandum_file_path` column exists
2. Check that memorandum was uploaded successfully
3. Ensure file exists in upload directory

### Paper type not displaying
1. Verify `paper` column exists in database
2. Check that paper value is 1, 2, or 3
3. Clear browser cache

## Testing Checklist

- [ ] Upload script with Paper 1 only
- [ ] Upload script with Paper 2 only
- [ ] Upload script with Paper 3 only
- [ ] Upload script with memorandum
- [ ] Upload script without memorandum
- [ ] Upload script with invalid file type (should fail)
- [ ] Upload script with large file >10MB (should fail)
- [ ] View script cards on student browse page
- [ ] Download script file
- [ ] Download memorandum file
- [ ] Delete shared script
- [ ] Verify badges display correctly
- [ ] Test responsive layout on mobile

## Support

For issues or questions, refer to the main `BROWSE-SCRIPTS-FEATURE.md` or contact the development team.
