<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto py-6">
    <h1 class="text-2xl font-semibold mb-2">Map Generation Editor</h1>
    <p class="text-sm text-gray-400 mb-6">Map ID: <?php echo e($mapId); ?> | Current State: <span id="map-state" class="font-mono"><?php echo e($state); ?></span></p>

    <div class="grid md:grid-cols-2 gap-6">
        <div class="space-y-4">
            <h2 class="text-lg font-medium">Generation Steps</h2>
            <ol class="space-y-2 list-decimal list-inside">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li>
                        <a href="<?php echo e($step['url']); ?>" class="text-blue-400 hover:text-blue-300 underline"><?php echo e($step['label']); ?></a>
                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </ol>
            <div id="next-step-container" class="mt-4 hidden" data-map-id="<?php echo e($mapId); ?>" data-initial-state="<?php echo e($state); ?>" data-is-generating="<?php echo e($isGenerating ? '1' : '0'); ?>">
                <a id="next-step-btn" href="#" class="inline-flex items-center px-3 py-2 bg-green-700 hover:bg-green-600 rounded text-sm font-semibold">Run Next Step →</a>
            </div>
        </div>
        <div class="space-y-4">
            <h2 class="text-lg font-medium">Actions</h2>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('mapgen.preview', ['mapId' => $mapId])); ?>" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm">Preview Tiles</a>
                <a href="/Map/load/<?php echo e($mapId); ?>/" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 rounded text-sm">Load (Legacy)</a>
                <a href="/game/<?php echo e($mapId); ?>/mapgen" class="px-3 py-2 bg-indigo-700 hover:bg-indigo-600 rounded text-sm">Start New Chain</a>
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
    <div class="mt-6 p-4 border border-gray-700 rounded bg-black/30">
        <h3 class="font-medium mb-2">Ask Assistant</h3>
        <div id="assistant-suggestion" class="text-sm text-gray-300">Press the button to get a suggestion for the next step.</div>
        <div class="mt-3 flex gap-2">
            <button id="ask-assistant" class="px-3 py-2 bg-blue-700 hover:bg-blue-600 rounded text-sm">Ask Assistant</button>
            <a id="apply-suggestion" href="#" class="px-3 py-2 bg-emerald-700 hover:bg-emerald-600 rounded text-sm hidden">Apply Suggestion</a>
        </div>
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

            // Ask Assistant UI wiring
            const askBtn = document.getElementById('ask-assistant');
            const sugEl = document.getElementById('assistant-suggestion');
            const applyEl = document.getElementById('apply-suggestion');
            askBtn?.addEventListener('click', async () => {
                try {
                    const resp = await fetch(`/api/ai/map-suggest/${mapId}`);
                    if (!resp.ok) return;
                    const data = await resp.json();
                    const route = data.nextRoute;
                    let text = data.title || 'Suggestion';
                    if (data.rationale) text += ` — ${data.rationale}`;
                    sugEl.textContent = text;
                    if (route){
                        // Add query params if present
                        let href = route;
                        if (data.params && Object.keys(data.params).length){
                            const q = new URLSearchParams(data.params).toString();
                            href = route + (route.includes('?') ? '&' : '?') + q;
                        }
                        applyEl.classList.remove('hidden');
                        applyEl.setAttribute('href', href);
                    } else {
                        applyEl.classList.add('hidden');
                    }
                } catch(e){
                    sugEl.textContent = 'Assistant unavailable right now.';
                    applyEl.classList.add('hidden');
                }
            });
        });
    </script>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mapgen/editor.blade.php ENDPATH**/ ?>