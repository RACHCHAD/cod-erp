# Deploying COD ERP to Hostinger VPS + Nginx

This guide assumes a Hostinger KVM VPS running Ubuntu 22.04+, PHP 8.3, MySQL 8.0+, and Nginx.

---

## 1. Server prerequisites

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y software-properties-common curl unzip git supervisor nginx

# PHP 8.3
sudo add-apt-repository -y ppa:ondrej/php
sudo apt update
sudo apt install -y php8.3-fpm php8.3-cli \
  php8.3-mysql php8.3-mbstring php8.3-xml php8.3-bcmath \
  php8.3-curl php8.3-zip php8.3-gd php8.3-intl php8.3-sqlite3 \
  php8.3-redis

# Composer
curl -sS https://getcomposer.org/installer | sudo php -- \
  --install-dir=/usr/local/bin --filename=composer

# Node 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# MySQL
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

---

## 2. Database setup

```sql
sudo mysql
CREATE DATABASE cod_erp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'cod_erp'@'127.0.0.1' IDENTIFIED BY 'StrongPasswordHere!';
GRANT ALL PRIVILEGES ON cod_erp.* TO 'cod_erp'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

---

## 3. Deploy the application

```bash
sudo mkdir -p /var/www
cd /var/www
sudo git clone https://github.com/RACHCHAD/cod-erp.git
sudo chown -R $USER:www-data cod-erp
cd cod-erp

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
# Edit .env: APP_URL, DB_*, MAIL_*, etc.
nano .env

php artisan migrate --force --seed
php artisan storage:link

npm ci
npm run build

# Cache config + routes + views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:cache-components

# Permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 4. Nginx server block

`/etc/nginx/sites-available/cod-erp`:

```nginx
server {
    listen 80;
    server_name erp.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name erp.example.com;

    root /var/www/cod-erp/public;
    index index.php;

    ssl_certificate     /etc/letsencrypt/live/erp.example.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/erp.example.com/privkey.pem;

    client_max_body_size 32m;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/cod-erp /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

### SSL with Let's Encrypt

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d erp.example.com
```

---

## 5. Queue worker (Supervisor)

`/etc/supervisor/conf.d/cod-erp-worker.conf`:

```ini
[program:cod-erp-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/cod-erp/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/cod-erp-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start cod-erp-worker:*
```

---

## 6. Scheduler (cron)

```bash
sudo crontab -u www-data -e
```

Add:

```cron
* * * * * cd /var/www/cod-erp && php artisan schedule:run >> /dev/null 2>&1
```

This handles recurring-expense generation and any future cron tasks.

---

## 7. PHP-FPM tuning (Hostinger VPS)

`/etc/php/8.3/fpm/pool.d/www.conf` (sample defaults are fine for small/mid traffic):

```ini
pm = dynamic
pm.max_children = 25
pm.start_servers = 4
pm.min_spare_servers = 4
pm.max_spare_servers = 10
```

`/etc/php/8.3/fpm/conf.d/99-opcache.ini`:

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
```

Reload PHP-FPM:

```bash
sudo systemctl reload php8.3-fpm
```

---

## 8. Upgrades & redeploys

```bash
cd /var/www/cod-erp
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan config:cache route:cache view:cache filament:cache-components
sudo supervisorctl restart cod-erp-worker:*
sudo systemctl reload php8.3-fpm nginx
```

---

## 9. Backups

Daily MySQL dump + media files:

```bash
sudo mkdir -p /var/backups/cod-erp
cat <<'EOF' | sudo tee /etc/cron.daily/cod-erp-backup
#!/usr/bin/env bash
set -e
TS=$(date +%Y%m%d_%H%M%S)
mysqldump -u cod_erp -p'StrongPasswordHere!' cod_erp | gzip > /var/backups/cod-erp/db_$TS.sql.gz
tar -czf /var/backups/cod-erp/storage_$TS.tar.gz -C /var/www/cod-erp storage/app
find /var/backups/cod-erp -type f -mtime +14 -delete
EOF
sudo chmod +x /etc/cron.daily/cod-erp-backup
```

---

## Troubleshooting

- **500 on first hit** — usually `storage/` permissions. Run the chown/chmod block in §3.
- **Mixed content / wrong scheme** — set `APP_URL=https://...` in `.env` and `php artisan config:cache`.
- **Queue jobs not running** — `sudo supervisorctl status` and check `/var/log/cod-erp-worker.log`.
- **Dashboard slow** — ensure `config:cache`, `route:cache`, `view:cache`, `filament:cache-components` are run.
