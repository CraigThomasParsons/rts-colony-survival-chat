<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\File;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use OpenAI;
use App\Services\AIPatch\PatchChunker;
use App\Services\AIPatch\PatchMerger;

class ProcessAIPatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 5;
    public $backoff = [5, 15, 45, 120];

    protected $instruction;

    public function __construct($instruction)
    {
        $this->instruction = $instruction;
    }

    public function handle()
    {
        // Directories we want GPT to see (safe + small)
        $paths = [
            base_path('app'),
            base_path('routes'),
            base_path('config'),
        ];

        // Break files into manageable chunks to avoid rate limits
        $chunks = PatchChunker::createChunks($paths);

        // OpenAI Client
        $client = OpenAI::client(env('OPENAI_API_KEY'));

        $mergedPatch = '';

        foreach ($chunks as $chunk) {

            // One GPT request per chunk
            $response = $client->responses()->create([
                'model' => 'gpt-4.1',
                'input' => [[
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'input_text',
                            'text' => "Instruction: {$this->instruction}\n\nChunk:\n{$chunk}"
                        ],
                    ],
                ]],
            ]);

            // Extract patch text from GPT output
            $patch = $response->output[0]['content'][0]['text'] ?? '';
            $mergedPatch .= "\n" . $patch;
        }

        // Merge all chunks into final patch
        $finalPatch = PatchMerger::merge($mergedPatch);

        // Save patch file
        $patchPath = base_path('storage/ai-patch/final.patch');
        File::ensureDirectoryExists(dirname($patchPath));
        File::put($patchPath, $finalPatch);

        // Apply patch to repo
        exec("cd " . base_path() . " && git apply storage/ai-patch/final.patch 2>&1", $out, $status);

        if ($status !== 0) {
            throw new \Exception('Patch failed: ' . implode("\n", $out));
        }
    }
}
