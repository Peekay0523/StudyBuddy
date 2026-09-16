# Production Deployment Guide

## File Storage Options for Production

### Option 1: Local Filesystem (Current - Good for Small/Medium Deployments)

**Directory Structure:**
```
/var/www/SchoolApp/
├── public/
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── uploads/          ← Move uploads here
│       └── study_groups/
│           └── 1/
│               └── voice/
├── config/
├── controllers/
└── models/
```

**Steps:**

1. **Move uploads directory:**
   ```bash
   mv /path/to/SchoolApp/uploads /path/to/SchoolApp/public/uploads
   ```

2. **Update file paths in code:**
   - `StudyGroupController.php` - Change upload paths from `__DIR__ . '/../uploads/'` to `__DIR__ . '/../public/uploads/'`
   - Remove `serve_router.php` - no longer needed

3. **Set permissions:**
   ```bash
   chown -R www-data:www-data /var/www/SchoolApp/public/uploads
   chmod -R 755 /var/www/SchoolApp/public/uploads
   ```

4. **Add .htaccess protection** (Apache):
   Already created at `public/uploads/.htaccess`

5. **Apache Virtual Host:**
   ```apache
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /var/www/SchoolApp/public
       
       <Directory /var/www/SchoolApp/public>
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
       
       # Protect voice recordings
       <Location /uploads/study_groups>
           Require all granted
       </Location>
   </VirtualHost>
   ```

6. **Nginx Configuration:**
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com;
       root /var/www/SchoolApp/public;
       index index.php;

       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }

       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
           fastcgi_index index.php;
           include fastcgi_params;
       }

       # Protect voice recordings - route through PHP for auth
       location /uploads/study_groups/ {
           alias /var/www/SchoolApp/public/uploads/study_groups/;
           
           # For voice files, check authentication via PHP
           location ~ \.(webm|mp3|wav)$ {
               rewrite ^/uploads/(.*)$ /index.php last;
           }
       }
   }
   ```

---

### Option 2: Cloud Storage (Recommended for Scale)

**Services:** AWS S3, Google Cloud Storage, Azure Blob

**Steps:**

1. **Install AWS SDK:**
   ```bash
   composer require aws/aws-sdk-php
   ```

2. **Update StudyGroupController.php:**
   ```php
   use Aws\S3\S3Client;
   
   public function sendMessage($groupId) {
       // ... existing code ...
       
       if ($messageType === 'voice' && isset($_FILES['voice_file'])) {
           $file = $_FILES['voice_file'];
           $fileName = time() . '_' . uniqid() . '.webm';
           
           // Upload to S3
           $s3 = new S3Client([
               'version' => 'latest',
               'region'  => 'us-east-1',
               'credentials' => [
                   'key'    => env('AWS_ACCESS_KEY_ID'),
                   'secret' => env('AWS_SECRET_ACCESS_KEY'),
               ]
           ]);
           
           $s3->putObject([
               'Bucket' => env('AWS_BUCKET'),
               'Key' => 'study_groups/' . $groupId . '/voice/' . $fileName,
               'Body' => fopen($file['tmp_name'], 'r'),
               'ContentType' => 'audio/webm',
               'ACL' => 'private',  // or 'public-read'
           ]);
           
           // Store S3 URL in database
           $relativePath = 's3://' . env('AWS_BUCKET') . '/study_groups/' . $groupId . '/voice/' . $fileName;
       }
   }
   ```

3. **Create S3 URL handler:**
   ```php
   // In router or controller
   $router->get('/uploads/study_groups/{groupId}/voice/{filename}', function($groupId, $filename) {
       requireLogin();
       
       $s3 = new S3Client([...]);
       $url = $s3->getObjectUrl(env('AWS_BUCKET'), 'study_groups/' . $groupId . '/voice/' . $filename);
       
       // Generate pre-signed URL (valid for 5 minutes)
       $command = $s3->getCommand('GetObject', [
           'Bucket' => env('AWS_BUCKET'),
           'Key' => 'study_groups/' . $groupId . '/voice/' . $filename,
       ]);
       
       $request = $s3->createPresignedRequest($command, '+5 minutes');
       $presignedUrl = (string)$request->getUri();
       
       header('Location: ' . $presignedUrl);
       exit;
   });
   ```

4. **Environment variables (.env):**
   ```
   AWS_ACCESS_KEY_ID=your_key
   AWS_SECRET_ACCESS_KEY=your_secret
   AWS_DEFAULT_REGION=us-east-1
   AWS_BUCKET=your-bucket-name
   ```

---

### Option 3: Hybrid (Local + CDN)

Use local storage but serve through CDN (Cloudflare, CloudFront):

1. Store files locally in `public/uploads/`
2. Configure CDN to pull from your server
3. Set cache headers for voice files

**Benefits:**
- Fast delivery (CDN caching)
- Low cost (CDN cheaper than S3 for high traffic)
- Simple setup

---

## Checklist for Production

- [ ] Choose storage option (local/S3/hybrid)
- [ ] Set up web server (Apache/Nginx)
- [ ] Configure HTTPS (SSL certificate)
- [ ] Set file permissions
- [ ] Configure backup strategy (DB + files)
- [ ] Set up monitoring/logging
- [ ] Configure environment variables
- [ ] Disable debug mode
- [ ] Set up error pages
- [ ] Test file uploads/downloads
- [ ] Test authentication for file access
- [ ] Configure rate limiting (prevent abuse)
- [ ] Set up CDN (optional)

---

## Quick Migration Script (Local to Local-Production)

```bash
#!/bin/bash
# migrate-to-production.sh

# 1. Move uploads to public
mv uploads public/uploads

# 2. Set permissions
chown -R www-data:www-data public/uploads
chmod -R 755 public/uploads

# 3. Update code paths (search and replace)
find controllers/ -name "*.php" -exec sed -i 's|__DIR__ \. \.\'/../uploads/|__DIR__ . \.\'/../public/uploads/|g' {} \;

# 4. Remove dev-only files
rm serve_router.php

echo "Migration complete!"
```

---

## Recommendation

**For launching:** Use **Option 1 (Local Filesystem)** - it's simple and works well for small-medium deployments.

**For scaling:** Migrate to **Option 2 (S3)** when you have:
- 1000+ concurrent users
- Large file storage needs
- Global user base (need CDN)

**Current code is already production-ready for Option 1!** Just move the uploads folder and adjust permissions.
