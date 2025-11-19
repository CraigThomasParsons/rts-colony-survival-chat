<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use OpenAI;

class ProcessAIPatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $instruction;

    /**
     * Create a new job instance.
     */
    public function __construct(string $instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $client = OpenAI::client(env('OPENAI_API_KEY'));

        // Read all project files
        $files = collect(Storage::disk('local')->allFiles())
            ->mapWithKeys(function ($file) {
                return [$file => Storage::disk('local')->get($file)];
            })
            ->toArray();

        // Create the request
        $response = $client->responses()->create([
            "model" => "gpt-4.1",
            "input" => [
                [
                    "role" => "user",
                    "content" => [
                        [ "type" => "text", "text" => "Generate a git patch. Instruction: {$this->instruction}" ],
                    ]
                ]
            ]
        ]);

        // Extract patch text
        $patch = $response->output_text ?? null;

        if (!$patch) {
            throw new \Exception("AI did not return a patch.");
        }

        // Save patch to storage
        $filename = 'ai_patches/patch_' . now()->format('Ymd_His') . '.diff';
        Storage::disk('local')->put($filename, $patch);
    }
}
