<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearMapData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'map:clear {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear all data from map, cell, and tile tables';

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

        $this->info('Clearing map data...');

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $tables = ['tile', 'cell', 'game_map', 'map'];

            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("Truncated table: {$table}");
                } else {
                    $this->warn("Table not found: {$table}");
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->info('Map data cleared successfully.');
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            $this->error('Error clearing map data: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
