<div wire:poll.5s class="p-6 space-y-4">
    <h1 class="text-xl font-bold">Queue Monitor</h1>

    <div class="grid grid-cols-2 gap-4 text-sm">
        <div><strong>Connection:</strong> {{ $stats['connection'] }}</div>
        <div><strong>Queue:</strong> {{ $stats['queue'] }}</div>

        <div>
            <strong>Pending Jobs:</strong>
            <span class="{{ $stats['pending_jobs'] > 0 ? 'text-yellow-600' : 'text-green-600' }}">
                {{ $stats['pending_jobs'] }}
            </span>
        </div>

        <div>
            <strong>Failed Jobs:</strong>
            <span class="{{ $stats['failed_jobs'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                {{ $stats['failed_jobs'] }}
            </span>
        </div>

        <div>
            <strong>Worker Alive:</strong>
            @if($stats['worker_alive'])
                <span class="text-green-600">YES</span>
            @else
                <span class="text-red-600">NO</span>
            @endif
        </div>

        <div>
            <strong>Last Job:</strong>
            {{ $stats['last_job_at'] ?? '—' }}
        </div>
    </div>
</div>

