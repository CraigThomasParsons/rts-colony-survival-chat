<?php

namespace App\Services;

use App\Models\Patch;
use Illuminate\Support\Facades\Log;
use OpenAI;
use OpenAI\Exceptions\RateLimitException;

class PatchService
{
    /**
     * Generate a unified git patch using OpenAI, with retry/backoff.
     */
    public function generatePatch(string $instruction): Patch
    {
        // Create the initial patch record
        $patch = Patch::create([
            'instruction' => $instruction,
            'status'      => 'generating',
        ]);

        Log::info("🧩 [PatchService] Starting patch generation for Patch #{$patch->id}");

        // Load OpenAI client and model from config/services.php
        $client = OpenAI::client(config('services.openai.key'));
        $model  = config('services.openai.model', 'gpt-5.1');

        // Retry parameters
        $maxAttempts = 5;
        $waitSeconds = 5;

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                Log::info("🧩 [PatchService] Calling OpenAI (attempt {$attempt}/{$maxAttempts}) using model '{$model}'");

                $response = $client->chat()->create([
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => "You generate unified Git patches for Laravel projects."],
                        ['role' => 'user', 'content' => $instruction],
                    ],
                ]);

                $diff = $response->choices[0]->message->content ?? null;

                if (!$diff) {
                    throw new \Exception("OpenAI returned no diff content.");
                }

                // Save generated diff
                $patch->update([
                    'diff'   => $diff,
                    'status' => 'generated',
                ]);

                Log::info("🧩 [PatchService] Patch #{$patch->id} generated successfully.");

                return $patch;

            } catch (RateLimitException $e) {
                Log::warning("⚠️ [PatchService] Rate limit hit on attempt {$attempt}/{$maxAttempts}. Waiting {$waitSeconds}s...");

                if ($attempt === $maxAttempts) {
                    Log::error("❌ [PatchService] Giving up after repeated rate limits.");

                    $patch->update([
                        'status'        => 'rate_limited',
                        'error_message' => $e->getMessage(),
                    ]);

                    return $patch;
                }

                sleep($waitSeconds);
                continue;

            } catch (\Throwable $e) {
                Log::error("❌ [PatchService] Unexpected failure: {$e->getMessage()}");

                $patch->update([
                    'status'        => 'failed',
                    'error_message' => $e->getMessage(),
                ]);

                return $patch;
            }
        }

        return $patch; // Should never reach here
    }
}
