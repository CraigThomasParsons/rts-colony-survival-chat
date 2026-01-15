#!/usr/bin/env bash
set -euo pipefail

USER_NAME="${USER_NAME:-$USER}"
PROJECT_ROOT="${PROJECT_ROOT:-/home/${USER_NAME}/Code/rts-colony-chat}"

cat <<NGINX
server {
    listen 80;
    server_name localhost;

    root ${PROJECT_ROOT}/public;
    index index.php index.html;

    access_log /var/log/nginx/rts-colony-access.log;
    error_log  /var/log/nginx/rts-colony-error.log;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \\.php$ {
        include        fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_pass   unix:/run/php-fpm/php-fpm.sock;
        fastcgi_index  index.php;
    }

    location ~ /\\.ht {
        deny all;
    }
}
NGINX

echo "[05-nginx-config-example] Above is an example Nginx server block. Save it to /etc/nginx/sites-available/rts-colony-chat.conf (or similar)."