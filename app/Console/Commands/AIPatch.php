<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessAIPatchJob;

class AIPatch extends Command
{
    protected $signature = 'ai:patch {instruction}';
    protected $description = 'Queue an AI-powered patch generation job';

    public function handle()
    {
        $instruction = $this->argument('instruction');

        ProcessAIPatchJob::dispatch($instruction);

        $this->info('AI Patch Job dispatched successfully.');
    }
}