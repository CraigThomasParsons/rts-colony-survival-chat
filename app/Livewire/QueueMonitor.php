<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;

class QueueMonitor extends Component
{
    public function getStats()
    {
        return [
            'connection' => config('queue.default'),
            'queue'      => 'default',

            'pending_jobs' => $this->pendingJobs(),
            'failed_jobs'  => $this->failedJobs(),

            'last_job_at' => Cache::get('queue:last_job_at'),
            'worker_alive' => $this->workerAlive(),
        ];
    }

    protected function pendingJobs(): int
    {
        try {
            return DB::table('jobs')->count();
        } catch (\Throwable $e) {
            return -1;
        }
    }

    protected function failedJobs(): int
    {
        try {
            return DB::table('failed_jobs')->count();
        } catch (\Throwable $e) {
            return -1;
        }
    }

    protected function workerAlive(): bool
    {
        return Cache::has('queue:heartbeat');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.queue-monitor', [
            'stats' => $this->getStats(),
        ])->layout('layouts.app');
    }
}

