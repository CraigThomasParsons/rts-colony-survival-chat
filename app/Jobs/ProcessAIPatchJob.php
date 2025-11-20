<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;use Illuminate\Contracts\Queue\ShouldQueue;use Illuminate\Foundation\Bus\Dispatchable;use Illuminate\Queue\InteractsWithQueue;use Illuminate\Queue\SerializesModels;use App\Services\PatchService;
class ProcessAIPatchJob implements ShouldQueue{use Dispatchable,InteractsWithQueue,Queueable,SerializesModels;protected $instruction;public function __construct($instruction){$this->instruction=$instruction;}public function handle(PatchService $service){$service->generatePatch($this->instruction);}}
