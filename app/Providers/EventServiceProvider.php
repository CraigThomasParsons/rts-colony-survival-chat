<?php

namespace App\Providers;

use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    
    public function boot(): void
    {
        Event::listen(JobProcessing::class, function (JobProcessing $event) {
            Cache::put('queue:heartbeat', now()->toDateTimeString(), now()->addSeconds(15));
            Cache::put('queue:last_job_at', now()->toDateTimeString(), now()->addMinutes(10));
        });

    }

}
