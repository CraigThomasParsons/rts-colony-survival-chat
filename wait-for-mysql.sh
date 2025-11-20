#!/bin/bash

echo "Waiting for MySQL..."

until mysql -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "select 1" >/dev/null 2>&1; do
    sleep 2
done

echo "MySQL is ready!"
