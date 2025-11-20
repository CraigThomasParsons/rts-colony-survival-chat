#!/bin/bash
set -e

REPORT="/tmp/codex-report.txt"
NOW="$(date '+%Y-%m-%d %H:%M:%S')"

echo "===== Codex QA Report ($NOW) =====" > "$REPORT"
echo "" >> "$REPORT"

echo ">> Restarting queue..." >> "$REPORT"
docker compose restart queue >> "$REPORT" 2>&1
echo "" >> "$REPORT"

echo ">> Dispatching test patch..." >> "$REPORT"
docker compose exec app php artisan ai:patch "AUTOTEST: // CODEX TEST PATCH" >> "$REPORT" 2>&1
echo "" >> "$REPORT"

echo ">> Queue logs (last 200 lines)..." >> "$REPORT"
docker compose logs queue --tail 200 >> "$REPORT" 2>&1
echo "" >> "$REPORT"

echo ">> Laravel log tail (200 lines)..." >> "$REPORT"
docker compose exec app tail -n 200 storage/logs/laravel.log >> "$REPORT" 2>&1
echo "" >> "$REPORT"

echo ">> Patches table (5 newest)..." >> "$REPORT"
docker compose exec mysql mysql -uroot -psecret -e "SELECT * FROM colony.patches ORDER BY id DESC LIMIT 5\G" >> "$REPORT"
echo "" >> "$REPORT"

echo ">> Jobs table (5 newest)..." >> "$REPORT"
docker compose exec mysql mysql -uroot -psecret -e "SELECT * FROM colony.jobs ORDER BY id DESC LIMIT 5\G" >> "$REPORT"
echo "" >> "$REPORT"

echo ">> Done." >> "$REPORT"

