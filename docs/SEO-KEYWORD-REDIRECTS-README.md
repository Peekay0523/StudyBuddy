# SEO Keyword Redirects Feature

## Overview
This feature redirects users to the dashboard when they search for specific keywords related to the platform.

## Keywords Covered
The following keywords will redirect users to the dashboard:

1. **Learning Platform variations:**
   - "Learning platorm" (common misspelling)
   - "Learning platform"

2. **Study Buddy variations:**
   - "Study Buddy"
   - "Study Buddie"
   - "Study Buddys"
   - "Study Buddies"
   - "Study Buddy website"
   - "Study Buddie website"

3. **Grade Improvement:**
   - "Better way to improve grades"

4. **Regional:**
   - "South Africa learning platform"

5. **Study Mate variations:**
   - "Study mate"
   - "Studymate"
   - "Study-mate"

## How It Works

### 1. URL-Based Redirects
Users can visit specific URLs that will redirect them to the dashboard:

```
/seo-keyword-redirect?q=learning+platform
/seo-keyword-redirect?q=study+buddy
/seo-keyword-redirect?q=better+way+to+improve+grades
```

### 2. Apache .htaccess Rewrites
If using Apache, the `.htaccess` file automatically rewrites SEO-friendly URLs:

```
/learning-platform → redirects to dashboard
/study-buddy → redirects to dashboard
/better-way-to-improve-grades → redirects to dashboard
```

### 3. Tracking
All keyword visits are tracked in the `seo_keyword_redirects` table for analytics.

## Installation

### Step 1: Create the Tracking Table
Run the migration script:

```bash
php create_seo_keyword_redirects_table.php
```

This creates the `seo_keyword_redirects` table to track keyword visits.

### Step 2: Configure Web Server

#### For Apache:
The `.htaccess` file is already configured. Just ensure `mod_rewrite` is enabled:

```apache
# Enable mod_rewrite in Apache configuration
a2enmod rewrite
service apache2 restart
```

#### For Nginx:
Add these rules to your Nginx configuration:

```nginx
# SEO Keyword Redirects
location ~ ^/learning-platform/?$ {
    return 301 /seo-keyword-redirect?q=learning%20platform;
}

location ~ ^/study-buddy/?$ {
    return 301 /seo-keyword-redirect?q=study%20buddy;
}

location ~ ^/study-buddie/?$ {
    return 301 /seo-keyword-redirect?q=study%20buddie;
}

location ~ ^/better-way-to-improve-grades/?$ {
    return 301 /seo-keyword-redirect?q=better%20way%20to%20improve%20grades;
}

location ~ ^/south-africa-learning-platform/?$ {
    return 301 /seo-keyword-redirect?q=south%20africa%20learning%20platform;
}

location ~ ^/study-mate/?$ {
    return 301 /seo-keyword-redirect?q=study%20mate;
}
```

#### For PHP Built-in Server:
The routes are already configured in `public/index.php`.

### Step 3: Test the Feature

Visit any of these URLs to test:

```
http://localhost:8000/seo-keyword-redirect?q=study+buddy
http://localhost:8000/learning-platform
http://localhost:8000/study-buddy
http://localhost:8000/better-way-to-improve-grades
```

All should redirect to: `/dashboard?ref=seo&keyword=...`

## Files Created

1. **Controller:**
   - `controllers/HomeController.php` (updated with `seoRedirect()` method)

2. **Routes:**
   - `public/index.php` (added SEO redirect route)

3. **Database:**
   - `create_seo_keyword_redirects_table.sql` (table schema)
   - `create_seo_keyword_redirects_table.php` (migration script)

4. **Web Server Config:**
   - `.htaccess` (Apache URL rewriting rules)

5. **Templates:**
   - `templates/pages/seo_landing.php` (SEO landing page)

6. **Documentation:**
   - `SEO-KEYWORD-REDIRECTS-README.md` (this file)

## Analytics

Track keyword performance by querying the database:

```sql
-- Get most popular keywords
SELECT keyword, COUNT(*) as visits 
FROM seo_keyword_redirects 
GROUP BY keyword 
ORDER BY visits DESC;

-- Get today's visits
SELECT * FROM seo_keyword_redirects 
WHERE DATE(visited_at) = DATE('now');

-- Get conversion rate (if you track conversions)
SELECT keyword, COUNT(*) as total_visits, SUM(converted) as conversions
FROM seo_keyword_redirects 
GROUP BY keyword;
```

## SEO Benefits

1. **Long-tail Keyword Coverage**: Captures various search term variations
2. **User Experience**: Directs users straight to the dashboard
3. **Analytics**: Tracks which keywords are driving traffic
4. **Flexibility**: Easy to add new keywords

## Adding New Keywords

To add new keywords, edit the `$keywords` array in `controllers/HomeController.php`:

```php
$keywords = [
    // Existing keywords...
    'your new keyword here',
    'another keyword'
];
```

## Customization

### Change Redirect Destination
To redirect to a different page, modify the redirect URL in `HomeController.php`:

```php
// Change from:
header('Location: /dashboard?ref=seo&keyword=' . urlencode($normalizedKeyword));

// To:
header('Location: /custom-page?ref=seo&keyword=' . urlencode($normalizedKeyword));
```

### Disable Tracking
To disable keyword tracking (not recommended), comment out the `trackKeywordVisit()` call:

```php
// $this->trackKeywordVisit($normalizedKeyword);
```

## Troubleshooting

### Redirects not working
1. Check that the route is registered in `public/index.php`
2. Verify `.htaccess` is enabled (for Apache)
3. Check file permissions on `.htaccess`

### Tracking not working
1. Ensure the `seo_keyword_redirects` table exists
2. Check database connection
3. Review error logs for SQL errors

### 404 Errors
1. Verify your web server is configured to use the router
2. Check that mod_rewrite is enabled (Apache)
3. Ensure the `.htaccess` file is in the root directory

## Best Practices

1. **Monitor Performance**: Regularly check which keywords are driving traffic
2. **Update Keywords**: Add new keywords based on search trends
3. **Test Regularly**: Periodically test redirects to ensure they're working
4. **Analytics Integration**: Consider integrating with Google Analytics for more detailed tracking

## Support

For issues or questions, check the application logs or contact the development team.
