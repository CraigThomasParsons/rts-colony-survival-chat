# Map Generation Queue System

This document explains the Laravel Queue-based map generation system implemented for the RTS Colony Survival Chat project.

## Overview

Map generation is a computationally intensive process that involves multiple sequential steps. To prevent timeouts and provide a better user experience, the entire generation pipeline runs asynchronously using Laravel's job queue system with job chaining.

## Architecture

### Job Chain Pattern

The system uses Laravel's `Bus::chain()` to execute map generation steps sequentially:

```php
Bus::chain([
    new RunMapGenerationStep($mapId, 'map:1init'),
    new RunMapGenerationStep($mapId, 'map:2firststep-tiles'),
    new RunMapGenerationStep($mapId, 'map:3tree-step1'),
    new RunMapGenerationStep($mapId, 'map:3tree-step2'),
    new RunMapGenerationStep($mapId, 'map:3tree-step3'),
    new RunMapGenerationStep($mapId, 'map:4water'),
    new RunMapGenerationStep($mapId, 'map:5mountain'),
])->dispatch();
```

Each job in the chain:
- Waits for the previous job to complete
- Executes its artisan command
- Logs output to `storage/logs/mapgen-{mapId}.log`
- Automatically triggers the next job in the chain

### Generation Steps

| Step | Command | Description |
|------|---------|-------------|
| 1 | `map:1init` | Generate height map using Fault Line algorithm and create initial cells |
| 2 | `map:2firststep-tiles` | Process tiles based on parent cell properties (land, water, rock, trees) |
| 3a | `map:3tree-step1` | First tree algorithm using Conway's Game of Life (20 iterations) |
| 3b | `map:3tree-step2` | Second tree algorithm with hole punching and orphan purging (5 iterations) |
| 3c | `map:3tree-step3` | Final tree refinement with minimal iterations (2 iterations) |
| 4 | `map:4water` | Process water tiles and boundaries |
| 5 | `map:5mountain` | Create mountain ridges and process mountain tiles |

## User Flow

### 1. Create New Game
```
POST /game
- User provides: name, width, height
- System creates Game and Map records
- Redirects to map generation form
```

### 2. Start Map Generation
```
POST /game/{mapId}/mapgen
- User provides: seed (optional), mountainLine (optional)
- System dispatches job chain
- Redirects to progress page
```

### 3. Monitor Progress
```
GET /game/{mapId}/progress
- Shows real-time log output via Server-Sent Events (SSE)
- Streams from storage/logs/mapgen-{mapId}.log
- Auto-refreshes as jobs execute
```

## Implementation Details

### RunMapGenerationStep Job

Location: `app/Jobs/RunMapGenerationStep.php`

Key features:
- Implements `ShouldQueue` interface
- Uses `Artisan::call()` to execute commands
- Captures and logs all output
- Handles exceptions and job failures
- Writes to dedicated log file per map

### Console Commands

All commands follow this pattern:

```php
protected $signature = 'map:X-name {mapId : The Map ID to process}';

public function handle(): int
{
    $mapId = $this->argument('mapId');
    $this->info("Starting step X for map {$mapId}...");
    
    // Processing logic here
    
    $this->info("Step X completed for map {$mapId}.");
    return self::SUCCESS;
}
```

### Queue Configuration

The system uses the default queue connection configured in `config/queue.php`:
- Driver: `database` (uses `jobs` table)
- Worker: Running in `rtschat-queue` container
- Command: `php artisan queue:work --verbose --tries=3 --timeout=90`

### Log Streaming

The progress page uses Server-Sent Events (SSE) to stream logs in real-time:

```javascript
const eventSource = new EventSource('/game/{mapId}/progress/stream');
eventSource.onmessage = function(event) {
    // Append log line to UI
    console.log(event.data);
};
```

## Benefits of This Approach

1. **Non-blocking**: User doesn't wait for long-running processes
2. **Resilient**: Failed jobs can be retried automatically
3. **Observable**: Real-time progress monitoring via logs
4. **Scalable**: Multiple maps can generate simultaneously
5. **Sequential**: Job chains ensure steps execute in correct order
6. **Debuggable**: All output captured in dedicated log files

## Monitoring & Debugging

### View Queue Status
```bash
docker compose exec app php artisan queue:work --verbose
```

### Check Failed Jobs
```bash
docker compose exec app php artisan queue:failed
```

### Retry Failed Jobs
```bash
docker compose exec app php artisan queue:retry all
```

### Clear Failed Jobs
```bash
docker compose exec app php artisan queue:flush
```

### View Map Generation Logs
```bash
docker compose exec app cat storage/logs/mapgen-{mapId}.log
```

### Tail Logs in Real-Time
```bash
docker compose exec app tail -f storage/logs/mapgen-{mapId}.log
```

## Future Enhancements

Potential improvements to the queue system:

1. **Progress Indicators**: Store step completion percentages in database
2. **Job Events**: Broadcast job status via WebSockets for real-time UI updates
3. **Parallel Processing**: Run independent steps (e.g., water + mountains) concurrently
4. **Priority Queues**: Allow premium users to jump the queue
5. **Job Batching**: Group related operations for better monitoring
6. **Horizon Dashboard**: Install Laravel Horizon for advanced queue monitoring
7. **Job Middleware**: Add rate limiting or resource throttling

## Related Files

- `app/Jobs/RunMapGenerationStep.php` - The core job class
- `app/Http/Controllers/GameController.php` - Job dispatching and progress monitoring
- `app/Console/Commands/HeightMapInit.php` - Step 1 command
- `app/Console/Commands/TileProcessing.php` - Step 2 command
- `app/Console/Commands/TreeProcessingStep*.php` - Step 3a-3c commands
- `app/Console/Commands/WaterProcessingCommand.php` - Step 4 command
- `app/Console/Commands/MountainProcessingCommand.php` - Step 5 command
- `resources/views/game/progress.blade.php` - Progress monitoring UI
- `docker-compose.yml` - Queue worker container configuration

## References

- [Laravel Queue Documentation](https://laravel.com/docs/10.x/queues)
- [Job Chaining](https://laravel.com/docs/10.x/queues#job-chaining)
- [Server-Sent Events](https://developer.mozilla.org/en-US/docs/Web/API/Server-sent_events)
- [Medium Article: Mastering Laravel Queue](https://medium.com/@selieshjksofficial/mastering-laravel-queue-advanced-background-processing-e9a3d94b9aff)
