# Bursaries Management Feature

## Overview
This feature allows administrators to manage bursaries available for students, and students can view available bursaries on the upload report card page.

## Setup Instructions

### 1. Create the Database Table
Run the following URL once in your browser to create the bursaries table:
```
http://localhost:8000/add_bursaries_table.php
```

This will:
- Create the `bursaries` table
- Add indexes for active bursaries and deadlines

### 2. Access Bursary Management (Admin)
After setting up the database table, admins can:
- Navigate to: `http://localhost:8000/admin/bursaries`
- Click "Add Bursary" to create new bursaries
- Edit, activate/deactivate, or delete existing bursaries

## Features

### Automatic Expiration
**Bursaries automatically expire when their deadline passes:**
- When the admin visits `/admin/bursaries`, any bursaries with past deadlines are automatically deactivated
- Expired bursaries are hidden from students immediately (they only see active, non-expired bursaries)
- Admins can view expired bursaries using the "Expired" filter
- Expired bursaries show an "Auto-deactivated" label and cannot be re-activated (only edited or deleted)

### Admin Features
1. **View All Bursaries** (`/admin/bursaries`)
   - Filter by: All, Active, Inactive, Expired
   - See deadline status (days remaining, expired)
   - Quick actions: Edit, Activate/Deactivate (non-expired only), Delete
   - Auto-deactivation: Expired bursaries are automatically deactivated when page loads

2. **Add New Bursary** (`/admin/bursaries/add`)
   - Name and provider
   - Eligibility criteria
   - What the bursary covers
   - Application deadline
   - Application URL
   - Contact information
   - Grade range requirements (min-max average)
   - Required subjects (multi-select)
   - Active/Inactive status

3. **Edit Bursary** (`/admin/bursaries/edit/{id}`)
   - Update all bursary information
   - Toggle active status

4. **Delete Bursary**
   - Remove bursaries that are no longer available

### Student Features
1. **View Available Bursaries** (`/upload-report-card`)
   - Appears below "Your Uploaded Report Cards" section
   - Shows only active, non-expired bursaries
   - Displays:
     - Bursary name and provider
     - Days left until deadline (highlighted if < 30 days)
     - Eligibility criteria
     - What it covers
     - Required subjects
     - Grade range requirements
     - Contact information
     - Apply Now button (if URL provided)
     - Search button (Google search for the bursary)

## Database Schema

```sql
CREATE TABLE bursaries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    provider TEXT NOT NULL,
    eligibility TEXT NOT NULL,
    covers TEXT DEFAULT '',
    deadline TEXT NOT NULL,
    contact TEXT DEFAULT '',
    apply_url TEXT DEFAULT '',
    min_grade_average REAL DEFAULT 0,
    max_grade_average REAL DEFAULT 100,
    required_subjects TEXT DEFAULT '[]',
    is_active INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

## API Endpoint

### GET `/api/get-available-bursaries`
Returns active, non-expired bursaries for students.

**Response:**
```json
{
    "success": true,
    "bursaries": [
        {
            "id": 1,
            "name": "Funza Lushaka Bursary",
            "provider": "Department of Basic Education",
            "eligibility": "South African citizens pursuing teaching...",
            "covers": "Tuition, accommodation, books",
            "deadline": "2026-12-31",
            "days_left": 286,
            "contact": "info@funzalushaka.gov.za",
            "apply_url": "https://example.com/apply",
            "min_grade_average": 60,
            "max_grade_average": 100,
            "required_subjects": ["Mathematics", "Physical Sciences"]
        }
    ],
    "count": 1
}
```

## Files Created/Modified

### New Files:
- `add_bursaries_table.sql` - SQL schema for bursaries table
- `add_bursaries_table.php` - PHP script to create the table
- `templates/pages/admin/bursaries.php` - Admin bursary list view
- `templates/pages/admin/bursaries_add_edit.php` - Add/Edit bursary form

### Modified Files:
- `controllers/AdminController.php` - Added bursary management methods
- `controllers/ReportCardController.php` - Added getAvailableBursaries() method
- `templates/pages/upload_report_card.php` - Added bursaries display section
- `templates/layouts/admin_header.php` - Added Bursaries navigation link
- `public/index.php` - Added bursary management routes

## Admin Routes
- `GET /admin/bursaries` - List all bursaries
- `GET /admin/bursaries/add` - Show add form
- `POST /admin/bursaries/create` - Create new bursary
- `GET /admin/bursaries/edit/{id}` - Show edit form
- `POST /admin/bursaries/update/{id}` - Update bursary
- `POST /admin/bursaries/delete` - Delete bursary
- `POST /admin/bursaries/toggle-status` - Toggle active status

## Student Routes
- `GET /api/get-available-bursaries` - Get available bursaries (JSON)
- `GET /upload-report-card` - View bursaries (displayed on this page)

## Usage Tips

### For Admins:
1. Add bursaries well before their deadlines
2. Keep bursaries active only if they're currently accepting applications
3. Deactivate expired bursaries instead of deleting them (for record-keeping)
4. Fill in as much detail as possible to help students make informed decisions

### For Students:
1. Check the upload-report-card page regularly for new bursaries
2. Pay attention to deadlines (shown with days remaining)
3. Use the "Apply Now" button when available
4. If no application URL is provided, use the "Search" button to find the application portal

## Future Enhancements (Optional)
- Add bursary categories/tags
- Add email notifications for new bursaries
- Add bursary application tracking
- Add filtering by grade range, subjects, or field of study
- Add bursary recommendations based on student's grades and subjects
