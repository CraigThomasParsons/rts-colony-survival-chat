# Install on the Metal (CachyOS/Arch, PHP 8.4 + LEMP)

This guide installs and runs rts-colony-survival-chat on CachyOS/Arch using a LEMP stack with PHP 8.4 FPM.

## 1) Prereqs
Install system packages:

```bash
sudo pacman -S --needed nginx mariadb mariadb-clients mariadb-libs nodejs npm
```

> PHP 8.4 is assumed already installed (php84). If you don’t have PHP 8.4 FPM, install it via your PHP 8.4 package source, then ensure `php84-fpm.service` exists.

## 2) Enable services

```bash
sudo systemctl enable --now mariadb
sudo systemctl enable --now php84-fpm
```

## 3) Initialize/upgrade MariaDB
If this is a fresh install:

```bash
sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
```

If MariaDB is already initialized, just run:

```bash
sudo mariadb-upgrade
```

## 4) Create DB + user

```bash
sudo mariadb -e "CREATE DATABASE IF NOT EXISTS rts_colony CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'rts_user'@'127.0.0.1' IDENTIFIED BY 'secret'; CREATE USER IF NOT EXISTS 'rts_user'@'localhost' IDENTIFIED BY 'secret'; GRANT ALL PRIVILEGES ON rts_colony.* TO 'rts_user'@'127.0.0.1'; GRANT ALL PRIVILEGES ON rts_colony.* TO 'rts_user'@'localhost'; FLUSH PRIVILEGES;"
```

## 5) Configure .env
Set MySQL values in .env:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rts_colony
DB_USERNAME=rts_user
DB_PASSWORD=secret
```

## 6) Nginx site config
Ensure Nginx loads vhosts:

```bash
sudo sed -i '/http {/a\\    include /etc/nginx/conf.d/*.conf;' /etc/nginx/nginx.conf
```

Create /etc/nginx/conf.d/rts-colony-survival.conf:

```nginx
server {
    listen 80;
    server_name game.elastigun.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name game.elastigun.com;
    root /home/craigpar/Code/rts-colony-survival-chat/public;

    ssl_certificate     /etc/nginx/certs/game.elastigun.com.crt;
    ssl_certificate_key /etc/nginx/certs/game.elastigun.com.key;

    add_header X-Frame-Options "SAMEORIGIN";
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
        fastcgi_pass unix:/run/php84-fpm/php-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

Create a self-signed cert (quick option):

```bash
sudo mkdir -p /etc/nginx/certs
sudo openssl req -x509 -nodes -newkey rsa:2048 -days 825 \
  -keyout /etc/nginx/certs/game.elastigun.com.key \
  -out /etc/nginx/certs/game.elastigun.com.crt \
  -subj "/CN=game.elastigun.com"
```

Create a trusted local cert with mkcert (recommended):

```bash
sudo pacman -S --needed mkcert nss
mkcert -install
mkcert -key-file /etc/nginx/certs/game.elastigun.com.key \
    -cert-file /etc/nginx/certs/game.elastigun.com.crt \
    game.elastigun.com
```

Reload Nginx:

```bash
sudo systemctl enable --now nginx
sudo nginx -t && sudo systemctl reload nginx
```

Add local DNS for the vhost (this machine only):

```bash
echo "127.0.0.1 game.elastigun.com" | sudo tee -a /etc/hosts
```

## 7) Install app deps
From the repo root:

```bash
composer install
npm install
npm run build
```

## 8) Migrate + seed map

```bash
php artisan migrate
php artisan map:1init
php artisan map:2firststep-tiles
php artisan map:3mountain
php artisan map:4water
```

## 9) Permissions

```bash
sudo chown -R http:http storage bootstrap/cache
```

If the app lives under a home directory (like /home/craigpar), allow the web user to traverse it:

```bash
sudo setfacl -m u:http:--x /home/craigpar
```

## 10) Open app
Visit: https://game.elastigun.com

If the browser still says DNS not resolved, disable Secure DNS in the browser or restart it, then retry. If you used the self-signed cert, add it to your OS trust store and restart the browser.

---

### Troubleshooting
- Check PHP-FPM socket: `/run/php84-fpm/php-fpm.sock`.
- View Nginx errors: `sudo journalctl -u nginx -n 100`.
- View PHP-FPM errors: `sudo journalctl -u php84-fpm -n 100`.
- Check MariaDB: `sudo systemctl status mariadb`.
