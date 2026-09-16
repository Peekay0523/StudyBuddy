# SEO Pages with Scripts & Memorandums - Setup Guide

## Overview

This feature adds 51 SEO-optimized career guidance pages to your StudySmart application, with:
- **Scripts & Memorandums Upload**: Upload and manage PDF/Word documents for each SEO page
- **Google AdSense Integration**: 4 ad placements per page (top, middle, bottom, sidebar)
- **Resource Downloads**: Track downloads and manage free/premium resources
- **Career Guidance Content**: 51 pre-built pages targeting low-competition keywords

## Features

### 1. Scripts & Memorandums
- Upload PDF and Word documents to any SEO page
- Organize by type: Script, Memorandum, Study Guide, Past Paper, Checklist
- Track download counts
- Set resources as free or premium
- Delete resources from admin panel

### 2. Google AdSense
- **4 ad placements per page**:
  - Top (after header)
  - Middle (after main content)
  - Bottom (before related pages)
  - Sidebar (in widget area)
- Configurable via environment variables
- Fallback placeholder when disabled

### 3. 51 SEO Pages Included

#### APS-Specific Pages (1-10)
- How to choose the right course after matric with APS 20-29

#### "What Course Should I Study" Series (11-22)
- What course should I study after matric with APS 20-29

#### Subject-Specific Pages (23-33)
- Courses for History, Maths Lit, Geography, Life Science, Physics, Business, Economics, Accounting, English, Setswana, Life Orientation

#### High Paying Jobs Series (34-43)
- High paying jobs requiring Geography, Life Science, English, Setswana, History, Accounting, Business, Physical Science, Mathematics

#### General Career Pages (45-51)
- Best courses to study in 2026
- Courses with high job opportunities
- Career quiz
- What to study if you like maths
- Best degrees for future jobs
- Easy courses to study
- Courses that pay well

## Installation

### Step 1: Run the Setup Script

```bash
cd C:\Users\mmereko\Desktop\SchoolApp\SchoolApp
php setup-seo-resources.php
```

This will:
- Create the `seo_resources` table
- Insert all 51 SEO pages
- Create the uploads directory
- Set up security configurations

### Step 2: Configure Google AdSense (Optional)

Add to your `.env` file:

```env
GOOGLE_ADSENSE_CLIENT_ID=ca-pub-XXXXXXXXXXXXXX
GOOGLE_ADSENSE_ENABLED=true
```

Then update `templates/components/adsense.php` with your actual ad slot IDs:

```php
$adSlots = [
    'top' => ['slot' => '1234567890', ...],
    'middle' => ['slot' => '2345678901', ...],
    'bottom' => ['slot' => '3456789012', ...],
    'sidebar' => ['slot' => '4567890123', ...]
];
```

### Step 3: Upload Resources

1. Log in as admin
2. Go to `/admin/seo/pages`
3. Click "Edit" on any page
4. Scroll to "Upload Scripts & Memorandums"
5. Select resource type, add title/description
6. Upload PDF or Word document
7. Click "Upload Resource"

## File Structure

```
SchoolApp/
├── add_seo_resources_table.sql       # Database migration
├── create_51_seo_pages.sql           # SEO pages data
├── setup-seo-resources.php           # Setup script
├── controllers/SEOController.php     # Updated with resource methods
├── models/SEOPage.php                # Updated with resource methods
├── templates/
│   ├── pages/seo_page.php            # Updated template
│   ├── components/adsense.php        # AdSense component (NEW)
│   └── pages/admin/seo_add_edit.php  # Updated with upload form
├── public/assets/css/seo-pages.css   # Updated styles
└── uploads/seo-resources/            # Resource files directory
```

## Database Schema

### seo_resources Table

```sql
CREATE TABLE seo_resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    page_id INTEGER NOT NULL,
    resource_type TEXT NOT NULL,      -- script, memorandum, study_guide, past_paper, checklist
    title TEXT NOT NULL,
    description TEXT,
    file_path TEXT NOT NULL,
    file_name TEXT NOT NULL,
    file_size INTEGER,
    file_mime_type TEXT,
    subject TEXT,
    grade_level TEXT,
    download_count INTEGER DEFAULT 0,
    is_free INTEGER DEFAULT 1,
    is_featured INTEGER DEFAULT 0,
    uploaded_by INTEGER,
    created_at DATETIME,
    updated_at DATETIME
);
```

## Routes

### Public Routes
```
GET  /seo/{slug}                           # View SEO page
GET  /seo-resource/download/{id}           # Download resource (tracks downloads)
```

### Admin Routes
```
GET  /admin/seo/pages                      # List all SEO pages
GET  /admin/seo/edit/{id}                  # Edit SEO page
POST /admin/seo/upload-resource/{id}       # Upload resource
GET  /admin/seo/delete-resource/{id}       # Delete resource
```

## Usage Examples

### Display Resources on Custom Template

```php
<?php
$seoModel = new SEOPage();
$resources = $seoModel->getResources($pageId);

foreach ($resources as $resource) {
    echo '<h3>' . htmlspecialchars($resource['title']) . '</h3>';
    echo '<a href="/seo-resource/download/' . $resource['id'] . '">Download</a>';
}
?>
```

### Upload Resource Programmatically

```php
$seoModel = new SEOPage();
$seoModel->addResource($pageId, [
    'resource_type' => 'memorandum',
    'title' => 'Grade 12 Mathematics Memorandum 2024',
    'description' => 'Complete memorandum with solutions',
    'file_path' => 'seo-resources/math_memorandum_2024.pdf',
    'file_name' => 'math_memorandum_2024.pdf',
    'file_size' => 1024000,
    'file_mime_type' => 'application/pdf',
    'is_free' => 1,
    'uploaded_by' => $userId
]);
```

## Security

- File uploads restricted to PDF and Word documents
- Maximum file size: 20MB
- Unique filenames generated to prevent overwrites
- `.htaccess` protection on uploads directory
- Admin authentication required for uploads

## SEO Best Practices

1. **Meta Tags**: Each page has optimized meta title, description, and keywords
2. **Schema Markup**: JSON-LD structured data for search engines
3. **Internal Linking**: Related pages section on each page
4. **Mobile Responsive**: All pages optimized for mobile devices
5. **Fast Loading**: Minimal CSS, optimized images

## Google AdSense Tips

1. **Place ads strategically**: Don't overwhelm users with ads
2. **Use responsive ad units**: Adapt to different screen sizes
3. **Monitor performance**: Use Google AdSense dashboard
4. **Comply with policies**: Follow AdSense program policies
5. **Test on mobile**: Ensure ads display correctly on all devices

## Troubleshooting

### Resources not uploading
- Check `uploads/seo-resources/` directory permissions (755)
- Verify file size is under 20MB
- Ensure file is PDF or Word document

### AdSense not showing
- Verify `GOOGLE_ADSENSE_CLIENT_ID` is set in `.env`
- Check that `GOOGLE_ADSENSE_ENABLED=true`
- Confirm ad slot IDs are correct in `adsense.php`

### Pages not appearing
- Run `php setup-seo-resources.php` to create pages
- Check page status is 'published' in database
- Clear browser cache

## Support

For issues or questions:
1. Check this README
2. Review error logs in `logs/` directory
3. Verify database migrations ran successfully

---

**Created**: March 2026
**Version**: 1.0
**Compatible**: StudySmart Application
