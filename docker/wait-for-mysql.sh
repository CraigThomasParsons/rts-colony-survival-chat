#!/bin/sh

HOST="${DB_HOST:-mysql}"
PORT="${DB_PORT:-3306}"
USER="${DB_USERNAME:-root}"
PASS="${DB_PASSWORD:-secret}"

echo "🔍 [wait-for-mysql] Waiting for MySQL on $HOST:$PORT..."

COUNT=0
MAX_RETRIES=60

while [ $COUNT -lt $MAX_RETRIES ]; do
    if mysqladmin ping -h"$HOST" -P"$PORT" -u"$USER" -p"$PASS" --silent; then
        echo "✅ [wait-for-mysql] MySQL is ready!"
        exit 0
    fi

    COUNT=$((COUNT+1))
    echo "⏳ [wait-for-mysql] Not ready yet... ($COUNT/$MAX_RETRIES)"
    sleep 2
done

echo "❌ [wait-for-mysql] MySQL did not become available after $MAX_RETRIES attempts."
exit 1
