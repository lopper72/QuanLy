# Deployment Guide: Production & Staging

This guide details the procedure for deploying the **Child Intervention Management System** (Laravel + Vue 3/Inertia.js + Tailwind CSS) to production and staging environments.

---

## 1. Server & System Requirements

Ensure the target server meets the following core specifications:

- **Operating System**: Linux (Ubuntu 22.04 LTS or newer recommended)
- **Web Server**: Nginx (preferred) or Apache
- **PHP Version**: PHP 8.2 or newer (CLI & FPM)
  - *Required Extensions*: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `hash`, `intl`, `json`, `libxml`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `xmlwriter`, `zip`
- **Database Engine**: MySQL 8.0+ or PostgreSQL 14+
- **Process Manager**: Supervisor (for queue workers)
- **Caching & Session Storage**: Redis 6+ (recommended)
- **Node.js**: v18.x or v20.x (only required on build servers, not strictly necessary on web servers if building assets beforehand)

---

## 2. Production Environment Variables (`.env`)

Before initiating the deployment process, configure your `/var/www/webquanly/.env` with these optimal production settings:

```ini
# Application configuration
APP_NAME="Child Intervention Management System"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATE_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://intervention.example.com

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=child_intervention_prod
DB_USERNAME=intervention_user
DB_PASSWORD=SecureProductionPassword123!

# Cache and Drivers
BROADCAST_CONNECTION=log
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Redis Config
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail setup (for report notifications)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@example.com
MAIL_PASSWORD=your_smtp_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="${APP_NAME}"

# Inertia Assets SSR (optional)
VITE_SSR_PORT=13714
```

---

## 3. Database Setup

1. Log into your database server and create a dedicated database and user:
   ```sql
   CREATE DATABASE child_intervention_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'intervention_user'@'localhost' IDENTIFIED BY 'SecureProductionPassword123!';
   GRANT ALL PRIVILEGES ON child_intervention_prod.* TO 'intervention_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
2. Set these credentials in your `.env` file before running migrations.

---

## 4. Step-by-Step Deployment Pipeline

Execute the following deployment steps sequentially in your server root path:

### Step 4.1: Pull Latest Codebase
```bash
# Fetch changes from repository
git fetch origin
git checkout main
git pull origin main
```

### Step 4.2: Install Backend Dependencies
Run Composer with optimized autoload mappings, removing development dependencies:
```bash
composer install --no-dev --optimize-autoloader
```

### Step 4.3: Install Frontend Dependencies & Build Assets
Ensure clean module installation and bundle Javascript/CSS with Vite:
```bash
# Clean install node modules
npm ci

# Build production asset bundle
npm run build
```

### Step 4.4: Run Database Migrations
Apply database schema modifications safely with the `--force` flag:
```bash
php artisan migrate --force
```

### Step 4.5: Configure Performance Optimization Cache
Cache routes, views, and configuration variables to prevent file lookup overhead:
```bash
# Cache configuration parameters
php artisan config:cache

# Cache routes mapping
php artisan route:cache

# Cache blade views
php artisan view:cache
```

### Step 4.6: Set Up Upload Storage Link
Generate a symlink from `public/storage` to `storage/app/public` to make uploaded records and reports accessible:
```bash
php artisan storage:link
```

---

## 5. File System Permissions

The web server (`www-data` on Ubuntu/Debian) needs read and write access to specific folders:

```bash
# Set ownership to web user and current deployer user
sudo chown -R www-data:www-data /var/www/webquanly
sudo chmod -R 775 /var/www/webquanly/storage
sudo chmod -R 775 /var/www/webquanly/bootstrap/cache
```

Ensure standard file creation permissions inside storage:
```bash
find /var/www/webquanly/storage -type d -exec chmod 775 {} \;
find /var/www/webquanly/storage -type f -exec chmod 664 {} \;
```

---

## 6. Queue Setup (Supervisor Configuration)

Because behavior logs, assessments, and system reports generate PDF documents or trigger background jobs, configure a supervisor queue worker:

Create a file named `/etc/supervisor/conf.d/intervention-worker.conf`:
```ini
[program:intervention-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/webquanly/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/webquanly/storage/logs/worker.log
stopwaitsecs=3600
```

Load and start the worker daemon:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start intervention-worker:*
```

---

## 7. Web Server Configuration (Nginx)

Below is the optimized Nginx block config for securing and running the Laravel app. Create/edit `/etc/nginx/sites-available/intervention.conf`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name intervention.example.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name intervention.example.com;
    root /var/www/webquanly/public;

    # SSL configuration (Let's Encrypt / Certbot)
    ssl_certificate /etc/letsencrypt/live/intervention.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/intervention.example.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static assets long-term (managed by Vite hashes)
    location ~* \.(?:css|js|jpg|jpeg|gif|png|ico|cur|gz|svg|svgz|mp4|ogg|ogv|webm|htc|woff|woff2)$ {
        expires 1y;
        access_log off;
        add_header Cache-Control "public, no-transform";
    }
}
```

Enable configuration and reload server:
```bash
sudo ln -s /etc/nginx/sites-available/intervention.conf /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 8. Backup & Maintenance Window Procedures

### Backup Script
Create a backup cron job (`/etc/cron.daily/intervention-backup`):
```bash
#!/bin/bash
BACKUP_DIR="/backups/intervention"
DATE=$(date +"%Y-%m-%d_%H-%M-%S")
mkdir -p "$BACKUP_DIR"

# Database Backup
mysqldump -u intervention_user -p'SecureProductionPassword123!' child_intervention_prod | gzip > "$BACKUP_DIR/db_$DATE.sql.gz"

# Storage Uploads Backup
tar -czf "$BACKUP_DIR/uploads_$DATE.tar.gz" -C /var/www/webquanly/storage/app/public .

# Retain backups for only 30 days
find "$BACKUP_DIR" -type f -mtime +30 -delete
```

### Enable Maintenance Mode
When performing updates or major data fixes:
```bash
# Go offline
php artisan down --secret="bypass-token-123"

# Re-enable app
php artisan up
```

---

## 9. Rollback Strategy

In case of a faulty release, execute this emergency rollback plan immediately:

1. **Revert Git Version**:
   ```bash
   git revert HEAD --no-edit # Or git checkout [previous_stable_commit_hash]
   ```
2. **Revert DB Migrations** (if rollback schemas are safe):
   ```bash
   php artisan migrate:rollback --step=1
   ```
3. **Rebuild Assets**:
   ```bash
   npm ci
   npm run build
   ```
4. **Purge Cache Configurations**:
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan cache:clear
   ```
5. **Restart Queue Worker daemon**:
   ```bash
   sudo supervisorctl restart intervention-worker:*
   ```
