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
    <p class="text-xs text-gray-400 mb-4">Live heightmap and tilemap visualizations refresh automatically.</p>
    <!-- jQuery UI Tabs for minimaps -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" defer></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js" defer></script>

    <div id="minimap-tabs" class="mt-2">
        <ul>
            <li><a href="#tab-steps">Generation Steps</a></li>
            <li><a href="#tab-heightmap">Heightmap</a></li>
            <li><a href="#tab-tilemap">Tilemap</a></li>
            <li><a href="#tab-log">Log Output</a></li>
        </ul>
        <div id="tab-steps" class="p-4">
            <h2 class="text-lg font-medium mb-2">Generation Steps</h2>
            <ol class="space-y-2 list-decimal list-inside">
                @foreach($steps as $step)
                    <li>
                        <a href="{{ $step['url'] }}" class="text-blue-400 hover:text-blue-300 underline">{{ $step['label'] }}</a>
                    </li>
                @endforeach
            </ol>
            <div id="next-step-container-tabs" class="mt-4 hidden" data-map-id="{{ $mapId }}" data-initial-state="{{ $state }}" data-is-generating="{{ $isGenerating ? '1' : '0' }}">
                <a id="next-step-btn-tabs" href="#" class="inline-flex items-center px-3 py-2 bg-green-700 hover:bg-green-600 rounded text-sm font-semibold">Run Next Step →</a>
            </div>
        </div>
        <div id="tab-heightmap" class="relative w-full" style="min-height:60vh;">
            <div class="flex items-center justify-between mb-2 text-xs text-gray-400">
                <div>Grid: <span id="heightmapDims">—</span></div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="heightmapZoom">Zoom</label>
                        <input id="heightmapZoom" type="range" min="1" max="8" step="1" value="1" />
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="heightmapFit">Fit</label>
                        <select id="heightmapFit" class="bg-gray-800 text-gray-200 rounded px-2 py-1">
                            <option value="none">None</option>
                            <option value="width">Width</option>
                            <option value="height">Height</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                </div>
            </div>
            <canvas id="heightmapCanvas" width="128" height="128" class="absolute inset-0 border border-gray-700 rounded bg-black/60" style="width:100%; height:100%; image-rendering: pixelated; transform-origin: top left;"></canvas>
        </div>
        <div id="tab-tilemap" class="relative w-full" style="min-height:60vh;">
            <div class="flex items-center justify-between mb-2 text-xs text-gray-400">
                <div>Grid: <span id="tilemapDims">—</span></div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <label for="tilemapZoom">Zoom</label>
                        <input id="tilemapZoom" type="range" min="1" max="8" step="1" value="1" />
                    </div>
                    <div class="flex items-center gap-2">
                        <label for="tilemapFit">Fit</label>
                        <select id="tilemapFit" class="bg-gray-800 text-gray-200 rounded px-2 py-1">
                            <option value="none">None</option>
                            <option value="width">Width</option>
                            <option value="height">Height</option>
                            <option value="both">Both</option>
                        </select>
                    </div>
                </div>
            </div>
            <canvas id="tilemapCanvas" width="128" height="128" class="absolute inset-0 border border-gray-700 rounded bg-black/60" style="width:100%; height:100%; image-rendering: pixelated; transform-origin: top left;"></canvas>
        </div>
        <div id="tab-log" class="p-2">
            <div class="panel">
                <div class="status-grid">
                    <div class="status-card">
                        <div class="status-title">Connection</div>
                        <div id="connStatus" class="status-value">Connecting…</div>
                        <div id="lastUpdate" class="status-sub">Last update: —</div>
                    </div>
                    <div class="status-card">
                        <div class="status-title">Current Step</div>
                        <div id="currentStep" class="status-value">—</div>
                        <div id="lineCount" class="status-sub">Lines: 0</div>
                    </div>
                    <div class="status-card" style="display:flex; flex-direction:column;">
                        <div class="status-title">Controls</div>
                        <div style="margin-top:0.5rem; display:flex; gap:0.5rem; flex-wrap:wrap;">
                            <button id="pauseBtn" class="btn btn-muted" style="padding:0.4rem 0.7rem;">Pause</button>
                            <button id="clearBtn" class="btn btn-muted" style="padding:0.4rem 0.7rem;">Clear</button>
                            <button id="copyBtn" class="btn btn-muted" style="padding:0.4rem 0.7rem;">Copy to Clipboard</button>
                            <button id="downloadBtn" class="btn btn-primary" style="padding:0.4rem 0.7rem;">Download</button>
                            <button id="reconnectBtn" class="btn btn-primary" style="padding:0.4rem 0.7rem;">Reconnect</button>
                        </div>
                        <div class="status-sub" style="margin-top:0.5rem;">
                            Auto-scroll:
                            <label style="display:inline-flex; align-items:center; margin-left:0.4rem;">
                                <input id="autoScroll" type="checkbox" checked />
                            </label>
                        </div>
                    </div>
                </div>
                <div class="terminal">
                    <div id="log" class="log"></div>
                </div>
            </div>
        </div>
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
            // Initialize jQuery UI tabs
            const initTabs = () => {
                if (window.jQuery && jQuery.fn.tabs) {
                    jQuery('#minimap-tabs').tabs();
                }
            };

            // Minimap polling and drawing
            const heightmapDataUrl = "{{ route('map.heightmap.data', ['mapId' => $mapId]) }}";
            // Log tab SSE wiring (reused from progress page)
            const sseUrl = "{{ route('game.mapgen.progress.stream', ['mapId' => $mapId]) }}";
            const logEl = document.getElementById('log');
            const connStatusEl = document.getElementById('connStatus');
            const lastUpdateEl = document.getElementById('lastUpdate');
            const currentStepEl = document.getElementById('currentStep');
            const lineCountEl = document.getElementById('lineCount');
            const pauseBtn = document.getElementById('pauseBtn');
            const clearBtn = document.getElementById('clearBtn');
            const copyBtn = document.getElementById('copyBtn');
            const downloadBtn = document.getElementById('downloadBtn');
            const reconnectBtn = document.getElementById('reconnectBtn');
            const autoScrollEl = document.getElementById('autoScroll');
            let es = null;
            let paused = false;
            let lineCount = 0;
            let buffer = [];
            let reconnectAttempts = 0;

            function setStatus(text, cls) {
                if (!connStatusEl) return;
                connStatusEl.textContent = text;
                connStatusEl.className = cls ? cls + " mt-1 text-sm font-medium" : "mt-1 text-sm font-medium";
            }

            function appendLine(line, metaClass = '') {
                if (!logEl) return;
                lineCount++;
                if (lineCountEl) lineCountEl.textContent = 'Lines: ' + lineCount;
                const node = document.createElement('div');
                node.textContent = line;
                if (metaClass) node.classList.add(metaClass);
                logEl.appendChild(node);
                if (autoScrollEl?.checked) {
                    logEl.scrollTop = logEl.scrollHeight;
                }
            }

            function detectStep(line) {
                const l = line.toLowerCase();
                if (l.includes('map:1init') || l.includes('heightmap')) return 'Initializing heightmap (map:1init)';
                if (l.includes('map:2firststep') || l.includes('tile')) return 'Creating tiles (map:2firststep-tiles)';
                if (l.includes('map:3mountain') || l.includes('mountain')) return 'Mountain step (map:3mountain)';
                if (l.includes('map:4water') || l.includes('water')) return 'Water step (map:4water)';
                if (l.match(/error|exception|fatal|failed/)) return 'ERROR';
                if (l.match(/completed|finished|success/)) return 'Completed';
                return null;
            }

            function connectSSE(){
                if (!sseUrl || !logEl) return;
                try {
                    es = new EventSource(sseUrl);
                    setStatus('Connected', 'text-green-400');
                    es.onmessage = (evt) => {
                        const line = evt.data;
                        if (!line) return;
                        const step = detectStep(line);
                        if (step && currentStepEl) currentStepEl.textContent = step;
                        if (!paused) appendLine(line);
                        if (lastUpdateEl) lastUpdateEl.textContent = 'Last update: ' + new Date().toLocaleTimeString();
                    };
                    es.onerror = () => {
                        setStatus('Disconnected — reconnecting…', 'text-yellow-400');
                        es.close();
                        setTimeout(connectSSE, Math.min(15000, 1000 * (++reconnectAttempts)));
                    };
                } catch(e){
                    setStatus('Connection failed', 'text-red-400');
                }
            }

            // Controls wiring (log tab)
            pauseBtn?.addEventListener('click', () => { paused = !paused; pauseBtn.textContent = paused ? 'Resume' : 'Pause'; });
            clearBtn?.addEventListener('click', () => { if (logEl) { logEl.innerHTML = ''; lineCount = 0; if (lineCountEl) lineCountEl.textContent = 'Lines: 0'; } });
            copyBtn?.addEventListener('click', () => { if (!logEl) return; const text = Array.from(logEl.childNodes).map(n => n.textContent).join('\n'); navigator.clipboard.writeText(text); });
            downloadBtn?.addEventListener('click', () => { if (!logEl) return; const text = Array.from(logEl.childNodes).map(n => n.textContent).join('\n'); const blob = new Blob([text], { type: 'text/plain' }); const url = URL.createObjectURL(blob); const a = document.createElement('a'); a.href = url; a.download = `mapgen-${"{{ $mapId }}"}.log`; a.click(); URL.revokeObjectURL(url); });
            reconnectBtn?.addEventListener('click', () => { es?.close(); connectSSE(); });
            const heightmapCanvas = document.getElementById('heightmapCanvas');
            const heightmapCtx = heightmapCanvas ? heightmapCanvas.getContext('2d') : null;
            const tilemapCanvas = document.getElementById('tilemapCanvas');
            const tilemapCtx = tilemapCanvas ? tilemapCanvas.getContext('2d') : null;
            const heightmapZoomEl = document.getElementById('heightmapZoom');
            const tilemapZoomEl = document.getElementById('tilemapZoom');
            const heightmapFitEl = document.getElementById('heightmapFit');
            const tilemapFitEl = document.getElementById('tilemapFit');
            const heightmapDimsEl = document.getElementById('heightmapDims');
            const tilemapDimsEl = document.getElementById('tilemapDims');
            let heightmapZoom = 1;
            let tilemapZoom = 1;
            let heightmapFit = 'none';
            let tilemapFit = 'none';

            function computeFitScale(canvas, fitMode, w, h) {
                if (!canvas || !w || !h) return 1;
                const rect = canvas.parentElement.getBoundingClientRect();
                const availW = rect.width;
                const availH = rect.height;
                const scaleW = availW / w;
                const scaleH = availH / h;
                if (fitMode === 'width') return scaleW;
                if (fitMode === 'height') return scaleH;
                if (fitMode === 'both') return Math.min(scaleW, scaleH);
                return 1; // none
            }

            async function fetchHeightmapData() {
                try {
                    const resp = await fetch(heightmapDataUrl, { cache: 'no-store' });
                    if (!resp.ok) return;
                    const data = await resp.json();
                    if (heightmapCtx) drawHeightmap(data);
                    if (tilemapCtx) drawTilemap(data);
                } catch (e) { /* ignore */ }
            }

            function drawHeightmap(data) {
                if (!data || !Array.isArray(data.grid) || !heightmapCtx) return;
                const grid = data.grid;
                const h = grid.length;
                const w = h ? grid[0].length : 0;
                if (!w || !h) return;
                const imageData = heightmapCtx.createImageData(w, h);
                let idx = 0;
                for (let y = 0; y < h; y++) {
                    const row = grid[y];
                    for (let x = 0; x < w; x++) {
                        const cell = row[x];
                        const val = Math.max(0, Math.min(255, cell?.h ?? 0));
                        imageData.data[idx++] = val;
                        imageData.data[idx++] = val;
                        imageData.data[idx++] = val;
                        imageData.data[idx++] = 255;
                    }
                }
                // Draw to internal resolution
                heightmapCanvas.width = w;
                heightmapCanvas.height = h;
                heightmapCtx.putImageData(imageData, 0, 0);
                // Update dims label
                if (heightmapDimsEl) heightmapDimsEl.textContent = `${w}×${h}`;
                // Apply zoom
                const fitScale = computeFitScale(heightmapCanvas, heightmapFit, w, h);
                const scale = (heightmapFit !== 'none') ? fitScale : heightmapZoom;
                heightmapCanvas.style.transform = `scale(${scale})`;
                // CSS scales to fit container (pixelated)
            }

            function drawTilemap(data) {
                if (!data || !Array.isArray(data.grid) || !tilemapCtx) return;
                const grid = data.grid;
                const h = grid.length;
                const w = h ? grid[0].length : 0;
                if (!w || !h) return;
                const imageData = tilemapCtx.createImageData(w, h);
                let idx = 0;
                for (let y = 0; y < h; y++) {
                    const row = grid[y];
                    for (let x = 0; x < w; x++) {
                        const cell = row[x];
                        const t = (cell?.type || '').toLowerCase();
                        let r=120,g=90,b=60; // default land
                        if (t.includes('water')) { r=30; g=80; b=200; }
                        else if (t.includes('tree')) { r=20; g=150; b=60; }
                        else if (t.includes('rock')) { r=130; g=130; b=130; }
                        imageData.data[idx++] = r;
                        imageData.data[idx++] = g;
                        imageData.data[idx++] = b;
                        imageData.data[idx++] = 255;
                    }
                }
                // Draw to internal resolution
                tilemapCanvas.width = w;
                tilemapCanvas.height = h;
                tilemapCtx.putImageData(imageData, 0, 0);
                // Update dims label
                if (tilemapDimsEl) tilemapDimsEl.textContent = `${w}×${h}`;
                // Apply zoom
                const fitScaleT = computeFitScale(tilemapCanvas, tilemapFit, w, h);
                const scaleT = (tilemapFit !== 'none') ? fitScaleT : tilemapZoom;
                tilemapCanvas.style.transform = `scale(${scaleT})`;
                // CSS scales to fit container (pixelated)
            }

            // Initial tabs init
            initTabs();

            // Initial draw and poll every 5s
            fetchHeightmapData();
            setInterval(fetchHeightmapData, 5000);
            // Zoom controls
            heightmapZoomEl?.addEventListener('input', (e) => {
                heightmapZoom = Number(e.target.value) || 1;
                // Re-apply transform immediately
                if (heightmapCanvas) {
                    const w = heightmapCanvas.width, h = heightmapCanvas.height;
                    const fitScale = computeFitScale(heightmapCanvas, heightmapFit, w, h);
                    const scale = (heightmapFit !== 'none') ? fitScale : heightmapZoom;
                    heightmapCanvas.style.transform = `scale(${scale})`;
                }
            });
            tilemapZoomEl?.addEventListener('input', (e) => {
                tilemapZoom = Number(e.target.value) || 1;
                if (tilemapCanvas) {
                    const w = tilemapCanvas.width, h = tilemapCanvas.height;
                    const fitScaleT = computeFitScale(tilemapCanvas, tilemapFit, w, h);
                    const scaleT = (tilemapFit !== 'none') ? fitScaleT : tilemapZoom;
                    tilemapCanvas.style.transform = `scale(${scaleT})`;
                }
            });
            heightmapFitEl?.addEventListener('change', (e) => {
                heightmapFit = e.target.value || 'none';
                // Re-apply transform based on fit
                if (heightmapCanvas) {
                    const w = heightmapCanvas.width, h = heightmapCanvas.height;
                    const fitScale = computeFitScale(heightmapCanvas, heightmapFit, w, h);
                    const scale = (heightmapFit !== 'none') ? fitScale : heightmapZoom;
                    heightmapCanvas.style.transform = `scale(${scale})`;
                }
            });
            tilemapFitEl?.addEventListener('change', (e) => {
                tilemapFit = e.target.value || 'none';
                if (tilemapCanvas) {
                    const w = tilemapCanvas.width, h = tilemapCanvas.height;
                    const fitScaleT = computeFitScale(tilemapCanvas, tilemapFit, w, h);
                    const scaleT = (tilemapFit !== 'none') ? fitScaleT : tilemapZoom;
                    tilemapCanvas.style.transform = `scale(${scaleT})`;
                }
            });

            // Connect SSE for log tab
            connectSSE();
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
@endsection
