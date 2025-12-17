<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use OpenAI;

class AIPatch extends Command
{
    protected $signature = 'ai:patch {prompt}';
    protected $description = 'Generate and apply a GPT patch to the Laravel project';

    public function handle()
    {
        $prompt = $this->argument('prompt');

        $this->info('🔍 Loading project files...');

        $paths = [
            app_path(),
            base_path('routes'),
            base_path('resources/js'),
            base_path('resources/views'),
            base_path('config'),
        ];

        $files = collect($paths)
            ->flatMap(fn($dir) => File::allFiles($dir))
            ->filter(fn($f) => in_array($f->getExtension(), ['php', 'js']))
            ->map(fn($f) => [
                'filename' => $f->getRealPath(),
                'content'  => File::get($f->getRealPath()),
            ]);

        $this->info('📡 Sending code to GPT...');

        $client = OpenAI::client(env('OPENAI_API_KEY'));

        $response = $client->responses()->create([
            'model' => 'gpt-4.1',
            'input' => [[
                'role'    => 'user',
                'content' => array_merge(
                    [
                        [
                            'type' => 'input_text',
                            'text' => "Create a unified git patch (diff) that can be applied with 'git apply'. Instruction: {$prompt}"
                        ]
                    ],
                    $files->map(fn($f) => [
                        'type' => 'input_text',
                        'text' => "FILENAME: {$f['filename']}\n\n{$f['content']}",
                    ])->toArray()
                ),
            ]],
        ]);

        $patch = $response->output[0]['content'][0]['text'] ?? '';

        $patchPath = base_path('ai.patch');
        file_put_contents($patchPath, $patch);

        $this->info("📄 Patch written to {$patchPath}");

        $this->info('📌 Applying patch...');
        exec("cd " . base_path() . " && git apply ai.patch 2>&1", $out, $status);

        if ($status !== 0) {
            $this->error('❌ Patch failed. Check ai.patch manually.');
            $this->line(implode("\n", $out));
        } else {
            $this->info('✅ Patch applied successfully.');
        }
    }
}
