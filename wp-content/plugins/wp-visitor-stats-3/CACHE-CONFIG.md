# Cache Configuration Guide for WP Visitor Stats

This guide explains how to configure your cache plugin to exclude WP Visitor Stats tracking endpoints, ensuring real-time statistics update correctly.

---

## LiteSpeed Cache

### Step 1: Access LiteSpeed Cache Settings

1. Log in to **WordPress Admin**
2. Go to **LiteSpeed Cache** → **Settings**
3. Click on the **"Excludes"** tab

### Step 2: Exclude URIs from Cache

In the **"Do Not Cache URIs"** section, add:

```
/wp-admin/admin-ajax.php
```

### Step 3: Exclude Query Strings

In the **"Do Not Cache Query Strings"** section, add these lines (one per line):

```
wp_visitor_stats_token
action=wp_visitor_stats_ping
action=wp_visitor_stats_get
action=wp_visitor_stats_dashboard_refresh
action=wp_visitor_stats_generate_cookie
```

### Step 4: Save and Purge Cache

1. Click **"Save Changes"**
2. Go to **LiteSpeed Cache** → **Toolbox** → **Purge** → **Purge All**
3. Clear the cache completely

### Alternative: Code in functions.php

If you prefer, add this code to your theme's `functions.php` file:

```php
// Exclude WP Visitor Stats from LiteSpeed Cache
add_filter('litespeed_cache_do_not_cache', function($do_not_cache) {
    if (isset($_GET['wp_visitor_stats_token']) || 
        (isset($_GET['action']) && strpos($_GET['action'], 'wp_visitor_stats') !== false)) {
        return true;
    }
    return $do_not_cache;
}, 10, 1);
```

---

## WP Rocket

### Step 1: Access WP Rocket Settings

1. Log in to **WordPress Admin**
2. Go to **Settings** → **WP Rocket**
3. Click on the **"Advanced Rules"** tab

### Step 2: Exclude URLs from Cache

In the **"Never Cache URL(s)"** section, add:

```
/wp-admin/admin-ajax.php
```

### Step 3: Exclude Query Strings

In the **"Cache Query String(s)"** section, add these query strings (one per line):

```
wp_visitor_stats_token
action=wp_visitor_stats_ping
action=wp_visitor_stats_get
action=wp_visitor_stats_dashboard_refresh
action=wp_visitor_stats_generate_cookie
```

### Step 4: Save and Clear Cache

1. Click **"Save Changes"**
2. Go to **WP Rocket** → **Tools** → **Clear and Preload Cache**
3. Click **"Clear Cache"**

### Alternative: Code in functions.php

Add this code to your theme's `functions.php` file:

```php
// Exclude WP Visitor Stats from WP Rocket cache
add_filter('rocket_cache_reject_uri', function($uri) {
    $uri[] = '/wp-admin/admin-ajax.php';
    return $uri;
});

add_filter('rocket_cache_query_strings', function($query_strings) {
    $query_strings[] = 'wp_visitor_stats_token';
    $query_strings[] = 'action';
    return $query_strings;
});
```

---

## WP Super Cache

### Step 1: Access WP Super Cache Settings

1. Log in to **WordPress Admin**
2. Go to **Settings** → **WP Super Cache**
3. Click on the **"Advanced"** tab

### Step 2: Exclude Pages from Cache

In the **"Accepted filenames and rejected URIs"** section, add these lines (one per line):

```
/wp-admin/admin-ajax.php
```

### Step 3: Exclude Query Strings

In the **"Rejected Query Strings"** section, add:

```
wp_visitor_stats_token
action=wp_visitor_stats_ping
action=wp_visitor_stats_get
action=wp_visitor_stats_dashboard_refresh
action=wp_visitor_stats_generate_cookie
```

### Step 4: Save and Delete Cache

1. Click **"Update Status"**
2. Go to **Settings** → **WP Super Cache** → **Contents** tab
3. Click **"Delete Cache"**

### Alternative: Code in wp-config.php

Add this to your `wp-config.php` file (before "That's all, stop editing!"):

```php
// Exclude WP Visitor Stats from WP Super Cache
define('WPSC_REJECTED_QUERY_STRING', 'wp_visitor_stats_token|action=wp_visitor_stats');
```

---

## W3 Total Cache

### Step 1: Access W3 Total Cache Settings

1. Log in to **WordPress Admin**
2. Go to **Performance** → **General Settings**
3. Scroll down to **"Page Cache"** section

### Step 2: Exclude Pages from Cache

1. Go to **Performance** → **Page Cache**
2. Scroll to **"Never cache the following pages"** section
3. Add:

```
/wp-admin/admin-ajax.php
```

### Step 3: Exclude Query Strings

1. In the same **Page Cache** settings page
2. Find **"Never cache the following pages"** or **"Rejected query strings"** section
3. Add these query strings (comma-separated or one per line):

```
wp_visitor_stats_token
action=wp_visitor_stats_ping
action=wp_visitor_stats_get
action=wp_visitor_stats_dashboard_refresh
action=wp_visitor_stats_generate_cookie
```

### Step 4: Save and Empty Cache

1. Click **"Save all settings"**
2. Go to **Performance** → **Dashboard**
3. Click **"Empty All Caches"**

### Alternative: Code in functions.php

Add this code to your theme's `functions.php` file:

```php
// Exclude WP Visitor Stats from W3 Total Cache
add_filter('w3tc_pagecache_reject_uri', function($uri) {
    $uri[] = '/wp-admin/admin-ajax.php';
    return $uri;
});

add_filter('w3tc_pagecache_reject_query_string', function($query_strings) {
    $query_strings[] = 'wp_visitor_stats_token';
    $query_strings[] = 'action';
    return $query_strings;
});
```

---

## Verification

After configuration, test your setup:

1. Visit your website
2. Statistics should update in real-time
3. The "Online Now" counter should work correctly
4. Check that visitor tracking endpoints are not cached by visiting:
   - `yoursite.com/wp-admin/admin-ajax.php?action=wp_visitor_stats_ping`
   - This should return fresh data, not cached content

---

## Troubleshooting

If statistics are still not updating correctly:

1. **Clear all caches** (browser cache, CDN cache if applicable, and plugin cache)
2. **Check browser console** for any JavaScript errors
3. **Verify exclusions** are correctly saved in your cache plugin settings
4. **Test in incognito/private mode** to avoid browser cache interference
5. **Check server logs** for any errors related to admin-ajax.php

---

## Need More Help?

If you continue to experience issues after following this guide, please:
- Check the plugin's admin panel for any error messages
- Review your cache plugin's documentation
- Ensure your WordPress and cache plugin are up to date

