# Bare-metal setup on Arch Linux

These steps let you run **rts-colony-survival-chat** directly on an Arch Linux host without docker-compose. They assume a fresh-ish Arch install and `sudo` access.

> **Note:** Commands are written for `zsh`, but they’re standard POSIX and should work in `bash` too.

---

## 1. Install system packages

Install PHP, web server, database, Redis, Node, and supporting tools.

```bash
sudo pacman -Syu --needed \
  nginx \
  php php-fpm php-gd php-pgsql php-intl php-xml php-curl php-zip php-sqlite \
  mariadb \
  redis \
  nodejs npm \
  git \
  unzip
```

Install Composer (if not already installed):

```bash
sudo pacman -Syu --needed composer
```

---

## 2. Configure PHP-FPM

Enable and configure PHP-FPM to work with Nginx.

1. Edit `/etc/php/php.ini` and ensure at least:

   - `cgi.fix_pathinfo = 0`
   - `extension=curl`
   - `extension=pdo_mysql`
   - `extension=zip`

   (Many of these may already be enabled; adjust as needed.)

2. Enable and start PHP-FPM:

```bash
sudo systemctl enable --now php-fpm
```

---

## 3. Initialize and configure MariaDB

Initialize the database and create a database + user for the game.

1. Initialize the data directory (first time only):

```bash
sudo mariadb-install-db --user=mysql --basedir=/usr --datadir=/var/lib/mysql
```

2. Enable and start MariaDB:

```bash
sudo systemctl enable --now mariadb
```

3. Run the secure installation helper:

```bash
sudo mysql_secure_installation
```

4. Create the `colony` database and a user (replace `strongpassword` with your real password):

```bash
mysql -u root -p <<'SQL'
CREATE DATABASE IF NOT EXISTS colony CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'colony_user'@'localhost' IDENTIFIED BY 'strongpassword';
GRANT ALL PRIVILEGES ON colony.* TO 'colony_user'@'localhost';
FLUSH PRIVILEGES;
SQL
```

---

## 4. Configure Redis

Enable Redis for queues/cache.

```bash
sudo systemctl enable --now redis
```

The default Redis config is generally fine for development.

---

## 5. Configure Nginx virtual host

We’ll serve the Laravel app from `/home/<user>/Code/rts-colony-chat/public`.

1. Create an Nginx server block, e.g. `/etc/nginx/sites-available/rts-colony-chat.conf` (you can adjust the path if you prefer Arch’s default single `nginx.conf` style):

```nginx
server {
    listen 80;
    server_name localhost;

    root /home/YOUR_USER/Code/rts-colony-chat/public;
    index index.php index.html;

    access_log /var/log/nginx/rts-colony-access.log;
    error_log  /var/log/nginx/rts-colony-error.log;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include        fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass   unix:/run/php-fpm/php-fpm.sock;
        fastcgi_index  index.php;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

2. If you use a `sites-available`/`sites-enabled` pattern, symlink it:

```bash
sudo mkdir -p /etc/nginx/sites-available /etc/nginx/sites-enabled
sudo ln -sf /etc/nginx/sites-available/rts-colony-chat.conf /etc/nginx/sites-enabled/rts-colony-chat.conf
```

3. Include `sites-enabled` from `/etc/nginx/nginx.conf` if not already present, by adding inside `http { ... }`:

```nginx
include /etc/nginx/sites-enabled/*.conf;
```

4. Test and restart Nginx:

```bash
sudo nginx -t
sudo systemctl enable --now nginx
sudo systemctl restart nginx
```

---

## 6. Clone repo and set permissions

If you haven’t already cloned the repo:

```bash
mkdir -p ~/Code
cd ~/Code
git clone git@github.com:CraigThomasParsons/rts-colony-survival-chat.git rts-colony-chat
cd rts-colony-chat
```

Set appropriate permissions so Laravel can write to `storage` and `bootstrap/cache`:

```bash
cd ~/Code/rts-colony-chat
mkdir -p storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
# Optionally set group to http (nginx user on Arch)
sudo chgrp -R http storage bootstrap/cache
sudo chmod -R g+w storage bootstrap/cache
```

---

## 7. Application bootstrap (.env, composer, migrations, assets)

From the project root (`~/Code/rts-colony-chat`):

1. Copy `.env` and generate an app key:

```bash
cp .env.example .env
php artisan key:generate
```

2. Edit `.env` and configure database and Redis (example):

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=colony
DB_USERNAME=colony_user
DB_PASSWORD=strongpassword

QUEUE_CONNECTION=database
CACHE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

3. Install PHP dependencies:

```bash
composer install
```

4. Run migrations and seeders as needed:

```bash
php artisan migrate
# Optional: php artisan db:seed
```

5. Install JS dependencies and build assets:

```bash
npm install
npm run build
```

For dev with hot-reload, you can run:

```bash
npm run dev
```

---

## 8. Queue worker and scheduler via systemd

For background jobs (map generation, AI patches, etc.), run a queue worker. For scheduled tasks (if used), configure the scheduler.

### 8.1 Queue worker service

Create a systemd unit `/etc/systemd/system/rts-queue.service`:

```ini
[Unit]
Description=RTS Colony Chat Laravel Queue Worker
After=network.target mariadb.service redis.service

[Service]
User=YOUR_USER
Group=YOUR_USER
WorkingDirectory=/home/YOUR_USER/Code/rts-colony-chat
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --timeout=120
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target
```

Then enable and start it:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now rts-queue.service
```

### 8.2 Scheduler service and timer

Create `/etc/systemd/system/rts-scheduler.service`:

```ini
[Unit]
Description=RTS Colony Chat Laravel Scheduler
After=network.target

[Service]
User=YOUR_USER
Group=YOUR_USER
WorkingDirectory=/home/YOUR_USER/Code/rts-colony-chat
ExecStart=/usr/bin/php artisan schedule:run
```

Create the timer `/etc/systemd/system/rts-scheduler.timer`:

```ini
[Unit]
Description=Run Laravel Scheduler every minute

[Timer]
OnBootSec=1min
OnUnitActiveSec=1min
Unit=rts-scheduler.service

[Install]
WantedBy=timers.target
```

Enable and start the timer:

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now rts-scheduler.timer
```

---

## 9. Useful local commands

From the project root:

- Run the app’s built-in PHP server (alternative to Nginx for quick tests):

  ```bash
  php -S 127.0.0.1:8000 -t public
  ```

- Run tests:

  ```bash
  php artisan test
  # or: ./vendor/bin/phpunit
  ```

- Run the existing Codex QA task (from this repo’s VS Code tasks):

  ```bash
  bash .codex/run-tests.sh
  ```

---

## 10. Quick setup via helper scripts

Instead of running everything by hand, you can use the helper scripts in this `setup/` directory:

- `01-install-packages.sh` – install required pacman packages.
- `02-setup-database.sh` – initialize MariaDB (if needed) and create the `colony` DB and user.
- `03-configure-env.sh` – copy `.env` and apply sane defaults for local dev.
- `04-app-bootstrap.sh` – run composer install, migrations, and asset build.
- `05-nginx-config-example.sh` – print an example Nginx server block to `stdout`.
- `06-systemd-queue.sh` – print a recommended `rts-queue.service` unit file.
- `07-systemd-scheduler.sh` – print recommended scheduler service/timer units.

Each script is documented and designed to be safe to run multiple times where practical.
