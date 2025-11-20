#!/bin/sh

HOST="${DB_HOST:-mysql}"
PORT="${DB_PORT:-3306}"
MAX_RETRIES="${MYSQL_MAX_RETRIES:-60}"
SLEEP_SECONDS="${MYSQL_SLEEP_SECONDS:-2}"

echo "🔍 [wait-for-mysql] Waiting for MySQL on ${HOST}:${PORT}..."

COUNT=0

while [ "$COUNT" -lt "$MAX_RETRIES" ]; do
    # TCP check instead of mysqladmin
    if nc -z "$HOST" "$PORT" >/dev/null 2>&1; then
        echo "✅ [wait-for-mysql] MySQL TCP port is open!"
        exit 0
    fi

    COUNT=$((COUNT + 1))
    echo "⏳ [wait-for-mysql] Not ready yet... (${COUNT}/${MAX_RETRIES})"
    sleep "$SLEEP_SECONDS"
done

echo "❌ [wait-for-mysql] MySQL did not become available after ${MAX_RETRIES} attempts."
exit 1
