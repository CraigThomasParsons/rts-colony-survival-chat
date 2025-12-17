You are running in Codex QA Autopilot Mode.

Repeat the following loop forever unless instructed otherwise:

**Reminder:** the full application stack (queues, mapgen, MySQL) only runs correctly inside Docker Compose. Use `bash .codex/run-tests.sh` or the "Run Codex QA" task so the `app`, `queue`, and `mysql` containers stay in sync. For quick sanity checks without Docker you can run `APP_STORAGE=$(pwd)/storage/testing php artisan test`, which now uses the SQLite test harness described in `docs/testing-sqlite.md`.

1. Read /tmp/codex-report.txt fully.
2. Extract:
   - queue status
   - patch generation status
   - rate limit errors
   - exceptions
   - DB changes
3. Summarize into a compact JSON block:
{
  "queue_state": "...",
  "openai_errors": [...],
  "patch_status": "...",
  "job_status": "...",
  "action_needed": "..."
}
4. If errors are recoverable (rate limit, missing model, slow MySQL):
   - advise me automatically.
5. When new errors appear, highlight them with warnings.

DO NOT STOP unless I type “STOP AUTOPILOT”.
