@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-2">Map Generation Editor</h1>
    <p class="text-sm text-gray-400 mb-6">Map ID: {{ $mapId }} | Current State: <span id="map-state" class="font-mono">{{ $state }}</span></p>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <h2 class="text-lg font-medium">Generation Steps</h2>
            <ol class="space-y-2 list-decimal list-inside">
                @foreach($steps as $step)
                    <li>
                        <a href="{{ $step['url'] }}" class="text-blue-400 hover:text-blue-300 underline">{{ $step['label'] }}</a>
                    </li>
                @endforeach
            </ol>
            <div id="next-step-container" class="mt-4 hidden" data-map-id="{{ $mapId }}" data-initial-state="{{ $state }}" data-is-generating="{{ $isGenerating ? '1' : '0' }}">
                <a id="next-step-btn" href="#" class="inline-flex items-center px-3 py-2 bg-green-700 hover:bg-green-600 rounded text-sm font-semibold">Run Next Step →</a>
            </div>
        </div>
        <div class="space-y-4">
            <h2 class="text-lg font-medium">Actions</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('mapgen.preview', ['mapId' => $mapId]) }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm">Preview Tiles</a>
                <a href="/Map/load/{{ $mapId }}/" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm">Load (Legacy)</a>
                <a href="/game/{{ $mapId }}/mapgen" class="px-3 py-2 bg-indigo-700 hover:bg-indigo-600 rounded text-sm">Start New Chain</a>
            </div>
            <p class="text-xs text-gray-500">Use the Start New Chain button only if generation is not currently locked.</p>
        </div>
    </div>

    <hr class="my-8 border-gray-700" />

    <h2 class="text-lg font-medium mb-2">Live Preview</h2>
    <p class="text-xs text-gray-400 mb-4">Below is an embedded placeholder for the upcoming interactive map editor canvas.</p>
    <div id="phaser-game" class="border border-gray-700 rounded bg-black/40 w-[640px] h-[640px] flex items-center justify-center text-gray-500 text-sm">
        Phaser Game Mount (auto-start if configured)
    </div>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            if (window.startFeudalFrontiersGame) {
                window.startFeudalFrontiersGame({ width: 640, height: 640, workerFrames: 8 });
            }
            // Polling logic to gate the Next Step button visibility.
            const container = document.getElementById('next-step-container');
            const btn = document.getElementById('next-step-btn');
            const stateEl = document.getElementById('map-state');
            const mapId = container?.dataset.mapId;
            if (!mapId) return;

            // States considered in-progress (button hidden)
            const inProgressStates = new Set([
                'Cell_Process_Started',
                'Tile_Process_Started',
                'Tree_Process_Started',
                'Tree_Three_Step'
            ]);

            // Mapping of completion states to next step route.
            const completionMapping = {
                'Map_Created_Initialized_Empty': `/Map/step1/${mapId}/`,
                'Cell_Process_Completed': `/Map/step2/${mapId}/`,
                'Tile_Process_Completed': `/Map/step3/${mapId}/`, // Adjust if named differently.
                'Tree_Second_Step': `/Map/treeStep3/${mapId}/`,
                'Tree_Process_Completed': `/Map/step4/${mapId}/`,
            };

            function evaluate(payload){
                const { state, is_generating, nextRoute } = payload;
                stateEl.textContent = state;
                const working = is_generating || inProgressStates.has(state);
                let route = nextRoute || completionMapping[state] || null;
                if (!working && route){
                    btn.setAttribute('href', route);
                    container.classList.remove('hidden');
                } else {
                    container.classList.add('hidden');
                }
            }

            async function poll(){
                try {
                    const resp = await fetch(`/api/map/${mapId}/status`);
                    if (!resp.ok) return;
                    const data = await resp.json();
                    evaluate(data);
                } catch(e){ /* ignore network errors */ }
            }

            // Initial evaluation
            evaluate({
                state: container.dataset.initialState,
                is_generating: container.dataset.isGenerating === '1',
                nextRoute: null
            });
            // Poll every 3s
            setInterval(poll, 3000);
        });
    </script>
</div>
@endsection
