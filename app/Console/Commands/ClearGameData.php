<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearGameData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:clear {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all data from game, map, cell, and tile tables';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->laravel->environment('production') && ! $this->option('force')) {
            if (! $this->confirm('You are in production. Do you wish to continue?')) {
                return;
            }
        }

        $this->info('Clearing game data...');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Order matters: delete dependent tables first
            $tables = ['tile', 'cell', 'game_map', 'map', 'games'];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("Truncated table: {$table}");
                } else {
                    $this->warn("Table not found: {$table}");
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('Game data cleared successfully.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error clearing game data: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
