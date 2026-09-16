# Browse Scripts by Grade Feature

## Overview
This feature allows administrators to upload study scripts that are shared with all students. Students can browse available scripts by their grade level and filter by subject.

## Features

### For Students
- **Browse Scripts by Grade**: Students can view all available scripts for their grade level
- **Filter by Subject**: Filter scripts by specific subjects (Mathematics, Physics, etc.)
- **View and Download**: Students can view scripts online or download them for offline study
- **Easy Access**: Grade selection cards on the upload script page for quick navigation

### For Admins
- **Upload Shared Scripts**: Admin can upload scripts that are available to all students
- **Manage Shared Scripts**: View, download, and delete shared scripts
- **Track Usage**: See which scripts have been uploaded and when

## Setup Instructions

### 1. Run the Database Migration

First, add the `is_shared` column to the `uploaded_scripts` table:

```bash
php migrate_add_is_shared_to_scripts.php
```

Or run the SQL directly:

```bash
mysql -u your_username -p your_database < add_is_shared_to_scripts.sql
```

### 2. Access the Feature

**For Students:**
1. Go to `/upload-script`
2. On the right side, you'll see "Browse Scripts by Grade" card
3. Click on your grade (8, 9, 10, 11, or 12)
4. View available scripts and filter by subject
5. Click "View" or "Download" on any script

**For Admins:**
1. Go to `/admin/scripts`
2. Use the "Upload Shared Script" form at the top
3. Fill in:
   - Title (e.g., "Mathematics Study Guide")
   - Subject (e.g., "Mathematics")
   - Grade Level (8-12)
   - Script File (PDF, DOCX, or TXT)
4. Click "Upload Shared Script"
5. Manage shared scripts in the "Shared Scripts" section below

## File Structure

```
SchoolApp/
├── controllers/
│   └── ScriptController.php          # Added browseScripts() and getBrowseScripts() methods
├── public/
│   └── index.php                      # Added routes for /browse-scripts/{grade}
├── templates/
│   └── pages/
│       ├── upload_script.php          # Added grade selection cards
│       └── browse_scripts.php         # New: Browse scripts page
├── templates/pages/admin/
│   └── scripts.php                    # Added shared script upload form
├── models/
│   └── UploadedScript.php             # No changes needed
└── migrate_add_is_shared_to_scripts.php  # Migration script
```

## Routes

### Student Routes
- `GET /browse-scripts/{grade}` - Browse scripts for a specific grade
- `GET /api/browse-scripts/{grade}` - API endpoint to get scripts JSON

### Admin Routes
- `POST /admin/scripts/upload-shared` - Upload a shared script

## Database Schema

### uploaded_scripts table (modified)

```sql
ALTER TABLE uploaded_scripts 
ADD COLUMN is_shared TINYINT(1) DEFAULT 0 COMMENT '0 = private (student uploaded), 1 = shared (admin uploaded)';

CREATE INDEX idx_is_shared ON uploaded_scripts(is_shared);
CREATE INDEX idx_grade_level ON uploaded_scripts(grade_level);
CREATE INDEX idx_subject ON uploaded_scripts(subject);
CREATE INDEX idx_browse ON uploaded_scripts(grade_level, is_shared, subject);
```

## How It Works

1. **Admin uploads a shared script**:
   - Admin goes to `/admin/scripts`
   - Fills out the upload form
   - Script is saved with `is_shared = 1`
   - Script is associated with a specific grade and subject

2. **Student browses scripts**:
   - Student clicks on their grade from the upload page
   - System queries: `SELECT * FROM uploaded_scripts WHERE grade_level = ? AND is_shared = 1`
   - Scripts are displayed in a grid layout
   - Student can filter by subject using the dropdown

3. **Student views/downloads**:
   - Click "View" to open the script in a new tab
   - Click "Download" to download the file

## UI Components

### Grade Selection Cards
Color-coded cards for each grade level:
- Grade 8: Blue
- Grade 9: Green
- Grade 10: Yellow
- Grade 11: Orange
- Grade 12: Purple

### Browse Scripts Page
- Header with back button
- Subject filter dropdown
- Grid of script cards with:
  - Title
  - Subject badge
  - Grade badge
  - View/Download buttons
  - Upload date

### Admin Upload Form
- Grid layout with responsive design
- Fields for title, subject, grade, and file
- File type validation (PDF, DOCX, TXT)
- File size limit (10MB)

## Security

- Only admins can upload shared scripts
- Students can only view/download shared scripts
- File type validation prevents malicious uploads
- File size limits prevent storage abuse

## Future Enhancements

Potential improvements:
- [ ] Add preview thumbnails for scripts
- [ ] Allow students to rate scripts
- [ ] Add download count tracking
- [ ] Implement script search functionality
- [ ] Add tags/categories for better organization
- [ ] Allow teachers to upload scripts (new role)
- [ ] Add script versioning
- [ ] Enable comments/discussion on scripts

## Troubleshooting

### Scripts not appearing for students
1. Check that `is_shared = 1` in the database
2. Verify the grade level matches
3. Clear browser cache

### Upload fails for admin
1. Check file size (max 10MB)
2. Verify file type (PDF, DOCX, TXT only)
3. Check `UPLOAD_DIR_SCRIPTS` permissions
4. Review PHP error logs

### Database errors
1. Ensure migration was run successfully
2. Check that all indexes were created
3. Verify `uploaded_scripts` table structure

## Testing

1. **Test admin upload**:
   ```bash
   # Login as admin
   # Go to /admin/scripts
   # Upload a test script for Grade 12 Mathematics
   ```

2. **Test student browsing**:
   ```bash
   # Login as student
   # Go to /upload-script
   # Click "Grade 12" card
   # Verify the uploaded script appears
   # Test view and download functionality
   ```

3. **Test filtering**:
   ```bash
   # Upload scripts for multiple subjects
   # Use the subject filter dropdown
   # Verify filtering works correctly
   ```

## Support

For issues or questions, contact the development team or check the main README.md.
