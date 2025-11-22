# Development Workflow Guide

## Connecting to WordPress for Live Development

There are several ways to develop this plugin while connected to a WordPress site, allowing you to see changes without reinstalling each time.

### Method 1: Symbolic Link (Recommended for Local Development)

This creates a live connection between your development repository and WordPress.

#### Setup Steps:

1. **Clone this repository** to your local machine:
```bash
git clone https://github.com/impact2021/wdta-membership.git
cd wdta-membership
```

2. **Create a symbolic link** from your WordPress plugins directory to this repository:

**On Mac/Linux:**
```bash
ln -s /path/to/your/wdta-membership /path/to/wordpress/wp-content/plugins/wdta-membership
```

**On Windows (Command Prompt as Administrator):**
```cmd
mklink /D "C:\path\to\wordpress\wp-content\plugins\wdta-membership" "C:\path\to\your\wdta-membership"
```

**Example:**
```bash
# If your repo is in ~/projects/wdta-membership
# And WordPress is in ~/sites/mywordpress
ln -s ~/projects/wdta-membership ~/sites/mywordpress/wp-content/plugins/wdta-membership
```

3. **Activate the plugin** in WordPress Admin:
   - Go to Plugins → Installed Plugins
   - Find "WDTA Membership"
   - Click "Activate"

4. **Make changes** in your repository:
   - Edit files in your `wdta-membership` repository
   - Save changes
   - Refresh WordPress page to see updates
   - No reinstallation needed!

#### Benefits:
- ✅ Changes are instant
- ✅ Work with your preferred editor/IDE
- ✅ Easy git operations
- ✅ Can test immediately in WordPress

---

### Method 2: Git Clone Directly in WordPress

Clone the repository directly into your WordPress plugins directory.

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/impact2021/wdta-membership.git
cd wdta-membership
```

Then:
1. Activate plugin in WordPress Admin
2. Make changes directly in this directory
3. Commit and push changes when ready

```bash
git add .
git commit -m "Your changes"
git push origin copilot/add-membership-access-plugin
```

#### Benefits:
- ✅ Simple setup
- ✅ Direct editing in WordPress environment

#### Drawbacks:
- ⚠️ Working directly in WordPress directory
- ⚠️ Need to be careful with file permissions

---

### Method 3: Watch and Sync Script

Use a sync script to automatically copy changes to WordPress.

Create `sync-to-wordpress.sh`:
```bash
#!/bin/bash

SOURCE_DIR="/path/to/your/wdta-membership"
DEST_DIR="/path/to/wordpress/wp-content/plugins/wdta-membership"

# Watch for changes and sync
while true; do
    rsync -av --delete \
        --exclude='.git' \
        --exclude='node_modules' \
        --exclude='.DS_Store' \
        "$SOURCE_DIR/" "$DEST_DIR/"
    sleep 2
done
```

Run it:
```bash
chmod +x sync-to-wordpress.sh
./sync-to-wordpress.sh
```

Or use a tool like `fswatch` (Mac) or `inotifywait` (Linux):
```bash
# Mac
fswatch -o /path/to/wdta-membership | xargs -n1 -I{} rsync -av --delete /path/to/wdta-membership/ /path/to/wordpress/wp-content/plugins/wdta-membership/

# Linux
while inotifywait -r -e modify,create,delete /path/to/wdta-membership; do
    rsync -av --delete /path/to/wdta-membership/ /path/to/wordpress/wp-content/plugins/wdta-membership/
done
```

---

### Method 4: Docker Development Environment

Use Docker to run WordPress with your plugin mounted.

**docker-compose.yml:**
```yaml
version: '3.8'

services:
  wordpress:
    image: wordpress:latest
    ports:
      - "8080:80"
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress
    volumes:
      - ./:/var/www/html/wp-content/plugins/wdta-membership
      - wordpress_data:/var/www/html
    depends_on:
      - db

  db:
    image: mysql:5.7
    environment:
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress
      MYSQL_ROOT_PASSWORD: rootpassword
    volumes:
      - db_data:/var/lib/mysql

volumes:
  wordpress_data:
  db_data:
```

Run:
```bash
docker-compose up -d
```

Access WordPress at `http://localhost:8080`

Changes in your repository are immediately reflected in the Docker container.

---

## Development Workflow

### Recommended Setup:

1. **Use Method 1 (Symbolic Link)** for local WordPress development
2. **Keep this repository** as your source of truth
3. **Make all changes** in the repository
4. **Test in WordPress** via the symlink
5. **Commit and push** when changes are working

### Day-to-Day Workflow:

```bash
# 1. Make changes in your editor
# Edit: includes/class-wdta-membership.php

# 2. Save file

# 3. Refresh WordPress page in browser
# Changes are immediately visible!

# 4. If adding new files or database changes:
# Deactivate and reactivate plugin in WordPress Admin

# 5. When satisfied, commit:
git add includes/class-wdta-membership.php
git commit -m "Add new feature"
git push origin copilot/add-membership-access-plugin
```

---

## Debugging in Development

### Enable WordPress Debug Mode

Edit `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
@ini_set('display_errors', 0);
```

View logs at: `wp-content/debug.log`

### Quick Debug Functions

Add to your code:
```php
// Log to debug.log
error_log('WDTA Debug: ' . print_r($variable, true));

// Display on screen (only when WP_DEBUG_DISPLAY is true)
echo '<pre>'; var_dump($variable); echo '</pre>';

// WordPress debug function
if (WP_DEBUG) {
    error_log('User ID: ' . $user_id);
}
```

### Browser Console Debugging

For JavaScript:
```javascript
console.log('WDTA Admin:', wdtaAdmin);
console.log('Response:', response);
```

---

## Testing Changes

### Database Changes

If you modify database schema:
```bash
# 1. Deactivate plugin in WordPress Admin
# 2. Make changes to class-wdta-database.php
# 3. Reactivate plugin
# Database tables will be recreated
```

Or manually run:
```php
// In WordPress admin or via WP-CLI
WDTA_Database::create_tables();
```

### Testing Email Notifications

```bash
# Method 1: Via WP-CLI
wp cron event run wdta_daily_email_check

# Method 2: Via URL
https://yoursite.com/wp-cron.php?doing_wp_cron
```

### Testing Cron Jobs

Install "WP Crontrol" plugin to:
- View scheduled events
- Run events manually
- Debug cron issues

### Testing Stripe Webhooks Locally

Use Stripe CLI:
```bash
# Install Stripe CLI
brew install stripe/stripe-cli/stripe

# Login
stripe login

# Forward webhooks to local WordPress
stripe listen --forward-to http://localhost:8080/wp-json/wdta/v1/stripe-webhook

# Test webhook
stripe trigger checkout.session.completed
```

---

## Common Development Tasks

### Clear WordPress Cache

```bash
# Via WP-CLI
wp cache flush

# Via PHP
wp_cache_flush();

# Or use a cache plugin and clear from admin
```

### Reset Plugin Data

```bash
# Via WP-CLI
wp db query "DROP TABLE IF EXISTS wp_wdta_memberships"
wp option delete wdta_stripe_public_key
wp option delete wdta_stripe_secret_key
# ... delete other options

# Then deactivate and reactivate plugin
```

### Check Database

```bash
# Via WP-CLI
wp db query "SELECT * FROM wp_wdta_memberships"

# Or use phpMyAdmin, Sequel Pro, TablePlus, etc.
```

---

## IDE/Editor Setup

### VS Code

Recommended extensions:
- PHP Intelephense
- WordPress Snippets
- PHP Debug (Xdebug)

**settings.json:**
```json
{
    "php.validate.executablePath": "/usr/bin/php",
    "intelephense.stubs": ["wordpress"],
    "intelephense.environment.phpVersion": "7.4.0"
}
```

### PHPStorm

1. Enable WordPress support:
   - Settings → Languages & Frameworks → PHP → Frameworks → WordPress
   - Enable WordPress integration
   - Set WordPress installation path

2. Configure PHP interpreter for your environment

---

## Troubleshooting

### Changes Not Appearing?

1. **Clear cache**: Browser, WordPress, and PHP opcache
2. **Check file permissions**: Should be readable by web server
3. **Verify symlink**: `ls -la /path/to/wordpress/wp-content/plugins/`
4. **Check for syntax errors**: Look in `debug.log`
5. **Deactivate/reactivate**: Sometimes needed for major changes

### Symlink Not Working?

- **Check permissions**: Web server needs read access to source files
- **Verify path**: Ensure absolute paths are correct
- **Try hard copy**: If symlink fails, use rsync method

### Database Changes Not Applied?

1. Deactivate plugin completely
2. Make database changes
3. Reactivate plugin
4. Check `debug.log` for errors

---

## Production Deployment

When ready to deploy to production:

1. **Test thoroughly** in development environment
2. **Commit all changes** to repository
3. **Create a release** or tag:
   ```bash
   git tag -a v1.0.0 -m "Release version 1.0.0"
   git push origin v1.0.0
   ```
4. **On production server**:
   ```bash
   cd /path/to/wordpress/wp-content/plugins/
   git clone https://github.com/impact2021/wdta-membership.git
   # Or upload via SFTP/FTP
   ```
5. **Activate** in WordPress Admin
6. **Configure settings** (Stripe keys, etc.)
7. **Test payment flow** before announcing

---

## Best Practices

### During Development:

- ✅ Make small, incremental changes
- ✅ Test each change immediately
- ✅ Commit working changes frequently
- ✅ Keep development and production configs separate
- ✅ Use Stripe test mode during development
- ✅ Back up database before major changes
- ✅ Document any manual configuration steps

### Git Workflow:

```bash
# Create feature branch for major changes
git checkout -b feature/new-payment-method

# Make changes and test

# Commit
git add .
git commit -m "Add PayPal payment method"

# Push
git push origin feature/new-payment-method

# Merge to main branch after testing
```

---

## Quick Reference

### File Locations:
- **Plugin files**: Where you cloned this repo
- **WordPress plugin**: `wp-content/plugins/wdta-membership/` (symlinked)
- **Debug log**: `wp-content/debug.log`
- **Database**: `wp_wdta_memberships` table

### Key Commands:
```bash
# Watch changes
tail -f /path/to/wordpress/wp-content/debug.log

# Clear cache
wp cache flush

# Run cron
wp cron event run wdta_daily_email_check

# Check database
wp db query "SELECT * FROM wp_wdta_memberships"

# Git status
git status

# Commit changes
git add .
git commit -m "Description"
git push
```

### Development URLs:
- **Local WordPress**: http://localhost/wordpress (or your local domain)
- **Admin**: http://localhost/wordpress/wp-admin
- **Plugin settings**: Admin → WDTA Membership → Settings
- **Webhooks**: http://localhost/wordpress/wp-json/wdta/v1/stripe-webhook

---

## Need Help?

- Check `wp-content/debug.log` for errors
- Review INSTALL.md for setup instructions
- Check ARCHITECTURE.md for system design
- Enable WP_DEBUG for detailed error messages

---

**TL;DR**: Use a symbolic link (Method 1) to connect your development repository to WordPress. Changes appear instantly without reinstalling!
