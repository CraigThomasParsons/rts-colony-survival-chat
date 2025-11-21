<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

/**
 * Job: RunMapGenerationStep
 *
 * Runs a single map generation artisan command (e.g. "map:1init") for a given map id.
 * The job appends the command output (and status) to storage/logs/mapgen-<mapId>.log.
 *
 * Usage:
 *   RunMapGenerationStep::dispatch($mapId, 'map:1init');
 *
 * Notes:
 * - The job uses Artisan::call() so output is collected via Artisan::output().
 * - Any thrown exception will be re-thrown so the queue worker can handle retry/backoff.
 */
class RunMapGenerationStep implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The map id the command targets.
     *
     * @var int|string
     */
    public $mapId;

    /**
     * The artisan command to run, e.g. "map:1init" or "map:2firststep-tiles".
     *
     * @var string
     */
    public $step;

    /**
     * Create a new job instance.
     *
     * @param int|string $mapId
     * @param string $step
     */
    public function __construct($mapId, string $step)
    {
        $this->mapId = $mapId;
        $this->step = $step;
    }

    /**
     * Execute the job.
     *
     * Calls the artisan command and appends timestamped output to the mapgen log.
     *
     * @return void
     *
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->appendLog("=== START {$this->step} for map {$this->mapId} ===");

        try {
            // Call artisan: pass mapId as an argument name 'mapId' if artisan command expects it.
            // Some commands may expect other argument names — adjust if necessary.
            $exitCode = Artisan::call($this->step, ['mapId' => $this->mapId]);

            $output = Artisan::output();

            if ($output !== null && $output !== '') {
                $this->appendLog($output);
            } else {
                $this->appendLog("(no output)");
            }

            $this->appendLog("=== END {$this->step} (exit code: {$exitCode}) ===");
        } catch (Exception $e) {
            // Record exception details to the log and rethrow so the queue can handle retries / failure hooks.
            $this->appendLog("!!! EXCEPTION during {$this->step} : " . $e->getMessage());
            $this->appendLog($e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Called when the job fails permanently (after retries).
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function failed(Exception $exception): void
    {
        $this->appendLog("### JOB FAILED: {$this->step} for map {$this->mapId}");
        $this->appendLog("Exception: " . $exception->getMessage());
        $this->appendLog($exception->getTraceAsString());
    }

    /**
     * Append a message to storage/logs/mapgen-<mapId>.log with a timestamp.
     *
     * @param string $message
     * @return void
     */
    protected function appendLog(string $message): void
    {
        $logFile = storage_path("logs/mapgen-{$this->mapId}.log");
        $timestamp = date('Y-m-d H:i:s');

        // Normalize line endings and ensure message ends with a newline.
        $message = trim($message, "\r\n") . PHP_EOL;

        // Prepend timestamp to each line in the message for clarity.
        $lines = explode(PHP_EOL, $message);
        $out = '';
        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }
            $out .= "[{$timestamp}] {$line}" . PHP_EOL;
        }

        // Ensure the logs directory exists and is writable.
        try {
            $dir = dirname($logFile);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            // Use file_put_contents with FILE_APPEND to minimize dependency on frameworks.
            @file_put_contents($logFile, $out, FILE_APPEND | LOCK_EX);
        } catch (Exception $e) {
            // Best-effort: do not break the job for log-write failures. Re-throwing could cause unnecessary retries.
        }
    }
}
