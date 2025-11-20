<?php

namespace App\Jobs;

use App\Models\Patch;
use App\Services\PatchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use OpenAI\Exceptions\RateLimitException;

class ProcessAIPatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $instruction;

    public function __construct(string $instruction)
    {
        $this->instruction = $instruction;
    }

    public function handle(PatchService $patches)
    {
        Log::info("🧩 [Job] Starting ProcessAIPatchJob for instruction: {$this->instruction}");

        try {
            // Generate or retrieve patch
            $patch = $patches->generatePatch($this->instruction);

            // If still rate limited, requeue
            if ($patch->status === 'rate_limited') {
                Log::warning("⚠️ [Job] Patch {$patch->id} hit OpenAI rate limit. Retrying in 30 seconds...");
                $this->release(30);
                return;
            }

            // If generation failed, fail early
            if ($patch->status === 'failed') {
                Log::error("❌ [Job] Patch {$patch->id} failed: {$patch->error_message}");
                return;
            }

            Log::info("🧩 [Job] Patch {$patch->id} generated successfully. Ready for apply step.");

        } catch (RateLimitException $e) {
            Log::warning("⚠️ [Job] Global rate limit in job handler. Retrying in 30s...");
            $this->release(30);
            return;

        } catch (\Throwable $e) {
            Log::error("❌ [Job] Fatal failure: {$e->getMessage()}");
            return;
        }
    }
}
