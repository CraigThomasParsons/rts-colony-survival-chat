@extends('layouts.app')
@section('content')
<div class="p-4">
    <h1 class="text-xl font-semibold mb-4">Feudal Frontiers</h1>
    <div id="phaser-game" class="border border-gray-700 bg-black/40 w-[640px] h-[640px]"></div>
</div>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        if (window.startFeudalFrontiersGame) {
            window.startFeudalFrontiersGame({ width: 640, height: 640, workerFrames: 8, rightClickQueue: true });
        }
    });
</script>
@endsection
