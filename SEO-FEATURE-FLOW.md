# SEO Feature Flow Diagram

## User Journey

```
┌─────────────────────────────────────────────────────────────────┐
│                        User Arrives                             │
└─────────────────────────────────────────────────────────────────┘
                              │
                              ▼
                    ┌─────────────────┐
                    │  What's the URL? │
                    └─────────────────┘
                              │
                ┌─────────────┴─────────────┐
                │                           │
                ▼                           ▼
        ┌───────────────┐           ┌──────────────────┐
        │   Homepage    │           │ SEO Keyword URL  │
        │      /        │           │ /seo-keyword-    │
        │               │           │ redirect?q=...   │
        └───────────────┘           └──────────────────┘
                │                           │
                │                           ▼
                │                   ┌──────────────────┐
                │                   │  Match Keyword?  │
                │                   └──────────────────┘
                │                           │
                │                  ┌────────┴────────┐
                │                  │                 │
                │                  ▼                 ▼
                │           ┌──────────┐      ┌──────────┐
                │           │   YES    │      │   NO     │
                │           └──────────┘      └──────────┘
                │                  │                 │
                │                  ▼                 │
                │         ┌─────────────────┐        │
                │         │ Track in DB     │        │
                │         │ (analytics)     │        │
                │         └─────────────────┘        │
                │                  │                 │
                │                  ▼                 │
                │         ┌─────────────────┐        │
                │         │ Redirect to     │        │
                │         │ Dashboard       │        │
                │         └─────────────────┘        │
                │                  │                 │
                └──────────────────┴─────────────────┘
                                   │
                                   ▼
                          ┌─────────────────┐
                          │  Landing Page   │
                          │  (seo_landing   │
                          │   .php)         │
                          └─────────────────┘
                                   │
                          ┌────────┴────────┐
                          │                 │
                          ▼                 ▼
                  ┌──────────────┐  ┌──────────────┐
                  │   Explore    │  │   Go to      │
                  │   Features   │  │   Dashboard  │
                  └──────────────┘  └──────────────┘
```

## Route Mapping

```
┌──────────────────────────────────────────────────────────────────┐
│                         URL Examples                             │
├──────────────────────────────────────────────────────────────────┤
│                                                                  │
│  LANDING PAGE (stays on page):                                  │
│  ──────────────────────────────────                             │
│  http://localhost:8000/                    → Landing Page       │
│  http://localhost:8000/home                → Landing Page       │
│                                                                  │
│  DASHBOARD REDIRECT (automatic redirect):                       │
│  ──────────────────────────────────────                         │
│  http://localhost:8000/seo-keyword-redirect?q=study+buddy       │
│                                            → Dashboard          │
│                                                                  │
│  http://localhost:8000/seo-keyword-redirect?q=learning+platform │
│                                            → Dashboard          │
│                                                                  │
│  http://localhost:8000/seo-keyword-redirect?q=better+way+to+    │
│  improve grades                             → Dashboard         │
│                                                                  │
└──────────────────────────────────────────────────────────────────┘
```

## File Structure

```
SchoolApp/
│
├── controllers/
│   └── HomeController.php
│       ├── landing()        → Shows landing page (/)
│       ├── seoRedirect()    → Handles keyword redirects
│       └── index()          → Original home page (backup)
│
├── templates/pages/
│   └── seo_landing.php      → Beautiful landing page
│
├── public/
│   └── index.php            → Routes configuration
│       ├── GET /            → HomeController@landing
│       └── GET /seo-keyword-redirect → HomeController@seoRedirect
│
└── .htaccess                → Apache URL rewriting
    ├── /study-buddy → /seo-keyword-redirect?q=study+buddy
    ├── /learning-platform → /seo-keyword-redirect?q=learning+platform
    └── ... (more keywords)
```

## Database Tracking

```
seo_keyword_redirects table
┌──────────┬──────────────┬──────────────┬─────────────┬──────────┐
│    id    │   keyword    │  visited_at  │  ip_address │  user_id │
├──────────┼──────────────┼──────────────┼─────────────┼──────────┤
│    1     │ study buddy  │ 2024-03-23   │ 192.168.1.1 │   NULL   │
│    2     │ learning     │ 2024-03-23   │ 192.168.1.2 │   NULL   │
│          │ platform     │              │             │          │
└──────────┴──────────────┴──────────────┴─────────────┴──────────┘
```

## Benefits of This Approach

✅ **Landing Page First**: New visitors see features before committing
✅ **SEO Optimized**: Keywords still drive traffic to dashboard
✅ **Flexible**: Users can choose to explore or go straight to dashboard
✅ **Trackable**: All keyword visits are recorded
✅ **User-Friendly**: No forced redirects for homepage visitors
