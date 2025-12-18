@extends('layouts.app')

@section('content')
@php
    // Map status for lifecycle gating UI.
    $map = \App\Models\Map::find($mapId);
    $mapStatus = $map?->status;
@endphp

<link rel="stylesheet" href="{{ asset('css/panel.css') }}">
<script src="https://cdn.jsdelivr.net/npm/typeit@8.8.3/dist/typeit.min.js" defer></script>

<style>
.step-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.6rem 0.8rem;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    border: 1px solid rgba(255, 255, 255, 0.05);
    transition: all 0.3s ease;
}

.step-item.running {
    background: rgba(59, 130, 246, 0.1);
    border-color: rgba(59, 130, 246, 0.3);
}

.step-item.completed {
    background: rgba(34, 197, 94, 0.08);
    border-color: rgba(34, 197, 94, 0.25);
}

.step-item.failed {
    background: rgba(239, 68, 68, 0.08);
    border-color: rgba(239, 68, 68, 0.25);
}

.step-icon {
    font-size: 1.2rem;
    min-width: 1.5rem;
    text-align: center;
}

.step-name {
    flex: 1;
    font-size: 0.9rem;
    color: #e0e7ff;
}

.step-time {
    font-size: 0.75rem;
    color: #94a3b8;
    min-width: 80px;
    text-align: right;
}

.step-item.completed .step-name {
    color: #86efac;
}

.step-item.failed .step-name {
    color: #fca5a5;
}

.step-item.running .step-icon {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}
</style>

<div class="panel">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">
        <div>
            <h1>Map Generation Progress — Map #{{ $mapId }}</h1>
            <div class="muted" style="font-size:0.85rem;">
                Streaming log: <code>storage/logs/mapgen-{{ $mapId }}.log</code>
            </div>
            @if ($map)
                <div class="muted" style="font-size:0.85rem; margin-top:0.35rem;">
                    Lifecycle status:
                    <code>{{ $mapStatus ?? '—' }}</code>
                    @if ($mapStatus === 'failed' && !empty($map->validation_errors))
                        <span class="text-red-300">(validation failed)</span>
                    @endif
                </div>
            @endif
        </div>

        <div style="text-align:right; display:flex; gap:0.5rem;">
            <!-- <a href="{{ url('/Map/load/'.$mapId.'/') }}" class="btn btn-primary">Open Map View</a> -->
            @if ($map)
                @if ($mapStatus === 'ready')
                    @php($gameId = $map->games()->orderBy('games.created_at')->value('games.id'))
                    <form method="POST" action="{{ $gameId ? route('game.start', ['game' => $gameId]) : route('maps.start', ['map' => $map->id]) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">Start Game</button>
                    </form>
                @else
                    <button class="btn btn-muted" disabled title="Map must be ready to start">Start Game</button>
                @endif
            @endif
            <a href="{{ route('control-panel') }}" class="btn btn-muted">Control Panel</a>
        </div>
    </div>

    <!-- Generation Steps Checklist -->
    <div class="status-card" style="margin-bottom:1.5rem;">
        <div class="status-title" style="margin-bottom:1rem;">Generation Steps</div>
        <div id="stepsChecklist" style="display:flex; flex-direction:column; gap:0.5rem;">
            <div class="step-item" data-step="map:1init">
                <span class="step-icon">⏳</span>
                <span class="step-name">1. Initialize Heightmap</span>
                <span class="step-time"></span>
            </div>
            <div class="step-item" data-step="map:2firststep-tiles">
                <span class="step-icon">⏳</span>
                <span class="step-name">2. Process Tiles</span>
                <span class="step-time"></span>
            </div>
            <div class="step-item" data-step="map:3tree-step1">
                <span class="step-icon">⏳</span>
                <span class="step-name">3a. Tree Algorithm (Step 1)</span>
                <span class="step-time"></span>
            </div>
            <div class="step-item" data-step="map:3tree-step2">
                <span class="step-icon">⏳</span>
                <span class="step-name">3b. Tree Algorithm (Step 2)</span>
                <span class="step-time"></span>
            </div>
            <div class="step-item" data-step="map:3tree-step3">
                <span class="step-icon">⏳</span>
                <span class="step-name">3c. Tree Algorithm (Step 3)</span>
                <span class="step-time"></span>
            </div>
            <div class="step-item" data-step="map:4water">
                <span class="step-icon">⏳</span>
                <span class="step-name">4. Process Water</span>
                <span class="step-time"></span>
            </div>
            <div class="step-item" data-step="map:5mountain">
                <span class="step-icon">⏳</span>
                <span class="step-name">5. Process Mountains</span>
                <span class="step-time"></span>
            </div>
            <div class="step-item" data-step="validation">
                <span class="step-icon">⏳</span>
                <span class="step-name">6. Validation</span>
                <span class="step-time"></span>
            </div>
        </div>
    </div>

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
        <div class="status-card">
            <div class="status-title">Queue Status</div>
            <div id="queuePending" class="status-value">—</div>
            <div id="queueFailed" class="status-sub">Failed: —</div>
            <div id="queueCurrent" class="status-sub" style="font-size:0.7rem; margin-top:0.25rem; color:#94a3b8;"></div>
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
        <div class="status-card">
            <div class="status-title">Stats</div>
            
            <div style="display:flex; gap:0.5rem;">
                <div style="flex:1;">
                    <div class="status-sub" style="margin-bottom:0.25rem;"></div>
                    <!-- <canvas
                        id="heightmapCanvas"
                        width="128"
                        height="128"
                        style="width:100%; border:1px solid #333; image-rendering: pixelated; background:#000;">
                    </canvas> -->
                    <div id="heightmapThresholds" class="text-xs text-gray-300 mt-2" style="line-height:1.3;">
                        Thresholds: <span class="text-gray-400">loading…</span>
                    </div>
                </div>
                <div style="flex:1;">
                    <div class="status-sub" style="margin-bottom:0.25rem;">Tilemap</div>
                    <canvas
                        id="tilemapCanvas"
                        width="128"
                        height="128"
                        style="width:100%; border:1px solid #333; image-rendering: pixelated; background:#000;">
                    </canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="terminal">
        <div id="log" class="log"></div>
    </div>
</div>

<script>
(function () {
    const mapId = '{{ $mapId }}';
    const sseUrl = "{{ route('game.mapgen.progress.stream', ['mapId' => $mapId]) }}";
    const heightmapDataUrl = "{{ route('map.heightmap.data', ['mapId' => $mapId]) }}";
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
    const heightmapCanvas = document.getElementById('heightmapCanvas');
    const heightmapCtx = heightmapCanvas ? heightmapCanvas.getContext('2d') : null;
    const tilemapCanvas = document.getElementById('tilemapCanvas');
    const tilemapCtx = tilemapCanvas ? tilemapCanvas.getContext('2d') : null;

    let es = null;
    let paused = false;
    let lineCount = 0;
    let buffer = [];
    let reconnectAttempts = 0;

    // TypeIt setup
    let typer = null;
    function initTyper() {
        if (typer || !window.TypeIt) return;
        try {
            typer = new TypeIt('#log', {
                speed: 28,
                cursor: true,
                lifeLike: true,
                waitUntilVisible: false,
            }).go();
        } catch(e) { /* noop */ }
    }
    // initialize once DOM is ready and script is loaded
    document.addEventListener('DOMContentLoaded', initTyper);
    window.addEventListener('load', initTyper);

    function setStatus(text, cls) {
        connStatusEl.textContent = text;
        connStatusEl.className = cls ? cls + " mt-1 text-sm font-medium" : "mt-1 text-sm font-medium";
    }

    function appendLine(line, metaClass = '') {
        lineCount++;
        lineCountEl.textContent = 'Lines: ' + lineCount;

        // Fallback if TypeIt is not yet loaded
        if (!typer) {
            initTyper();
            const node = document.createElement('div');
            node.textContent = line;
            if (metaClass) node.classList.add(metaClass);
            logEl.appendChild(node);
            if (autoScrollEl.checked) {
                logEl.scrollTop = logEl.scrollHeight;
            }
            return;
        }

        // Ensure styling consistency via spans
        const safeLine = line.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const prefix = metaClass ? `<span class="${metaClass}">` : '';
        const suffix = metaClass ? `</span>` : '';
        // Queue typing of the line with a newline
        typer
            .type(prefix + safeLine + suffix)
            .break()
            .exec(() => { if (autoScrollEl.checked) { logEl.scrollTop = logEl.scrollHeight; } });
    }

    function detectStep(line) {
        // simple step detection rules based on expected log content
        const l = line.toLowerCase();
        if (l.includes('map:1init') || l.includes('heightmap')) return 'Initializing heightmap (map:1init)';
        if (l.includes('map:2firststep') || l.includes('first step') || l.includes('tile')) return 'Creating tiles (map:2firststep-tiles)';
        if (l.includes('map:3mountain') || l.includes('mountain')) return 'Mountain step (map:3mountain)';
        if (l.includes('map:4water') || l.includes('water')) return 'Water step (map:4water)';
        if (l.match(/error|exception|fatal|failed/)) return 'ERROR';
        if (l.match(/completed|finished|success/)) return 'Completed';
        return null;
    }

    function updateStepChecklist(line) {
        const l = line.toLowerCase();
        
        // Detect which step is starting
        if (l.includes('=== start')) {
            if (l.includes('map:1init')) {
                markStepRunning('map:1init');
            } else if (l.includes('map:2firststep-tiles')) {
                markStepRunning('map:2firststep-tiles');
            } else if (l.includes('map:3tree-step1')) {
                markStepRunning('map:3tree-step1');
            } else if (l.includes('map:3tree-step2')) {
                markStepRunning('map:3tree-step2');
            } else if (l.includes('map:3tree-step3')) {
                markStepRunning('map:3tree-step3');
            } else if (l.includes('map:4water')) {
                markStepRunning('map:4water');
            } else if (l.includes('map:5mountain')) {
                markStepRunning('map:5mountain');
            }
        }
        
        // Detect which step is completing
        if (l.includes('=== end')) {
            const exitCodeMatch = line.match(/exit code:\s*(\d+)/);
            const exitCode = exitCodeMatch ? parseInt(exitCodeMatch[1]) : 0;
            const success = exitCode === 0;
            
            if (l.includes('map:1init')) {
                markStepComplete('map:1init', success);
            } else if (l.includes('map:2firststep-tiles')) {
                markStepComplete('map:2firststep-tiles', success);
            } else if (l.includes('map:3tree-step1')) {
                markStepComplete('map:3tree-step1', success);
            } else if (l.includes('map:3tree-step2')) {
                markStepComplete('map:3tree-step2', success);
            } else if (l.includes('map:3tree-step3')) {
                markStepComplete('map:3tree-step3', success);
            } else if (l.includes('map:4water')) {
                markStepComplete('map:4water', success);
            } else if (l.includes('map:5mountain')) {
                markStepComplete('map:5mountain', success);
            }
        }
        
        // Detect validation
        if (l.includes('validating') || l.includes('validation')) {
            markStepRunning('validation');
        }
        if (l.includes('validation') && (l.includes('complete') || l.includes('success') || l.includes('passed'))) {
            markStepComplete('validation', true);
        }
        if (l.includes('validation') && (l.includes('fail') || l.includes('error'))) {
            markStepComplete('validation', false);
        }
    }

    function markStepRunning(stepName) {
        const stepEl = document.querySelector(`.step-item[data-step="${stepName}"]`);
        if (!stepEl) return;
        
        stepEl.classList.remove('completed', 'failed');
        stepEl.classList.add('running');
        stepEl.querySelector('.step-icon').textContent = '🔄';
        stepEl.querySelector('.step-time').textContent = 'Running...';
    }

    function markStepComplete(stepName, success) {
        const stepEl = document.querySelector(`.step-item[data-step="${stepName}"]`);
        if (!stepEl) return;
        
        stepEl.classList.remove('running');
        stepEl.classList.add(success ? 'completed' : 'failed');
        stepEl.querySelector('.step-icon').textContent = success ? '✅' : '❌';
        
        const now = new Date();
        const timeStr = now.toLocaleTimeString();
        stepEl.querySelector('.step-time').textContent = timeStr;
    }
    }

    function classifyLine(line) {
        const l = line.toLowerCase();
        if (l.match(/error|exception|fatal|failed/)) return 'text-red-400';
        if (l.match(/warning|warn/)) return 'text-yellow-300';
        if (l.match(/completed|finished|success/)) return 'text-green-300';
        return '';
    }

    async function fetchHeightmapData() {
        if (!heightmapCtx && !tilemapCtx) return;
        try {
            const resp = await fetch(heightmapDataUrl, { cache: 'no-store' });
            if (!resp.ok) {
                console.warn('Failed to load heightmap data', resp.status);
                return;
            }
            const data = await resp.json();
            if (heightmapCtx) drawHeightmap(data);
            if (tilemapCtx) drawTilemap(data);
            // Update thresholds legend if present
            const thrEl = document.getElementById('heightmapThresholds');
            if (thrEl && data && data.thresholds) {
                const wm = data.thresholds.waterMax ?? '—';
                const fh = data.thresholds.foothills ?? data.thresholds.low ?? '—';
                const pk = data.thresholds.peaks ?? data.thresholds.high ?? '—';
                thrEl.innerHTML = `Water ≤ <span class="text-blue-300">${wm}</span> · Foothills ≥ <span class="text-amber-300">${fh}</span> · Peaks ≥ <span class="text-red-300">${pk}</span>`;
            }
        } catch (e) {
            console.error('Error fetching heightmap data', e);
        }
    }

    function drawHeightmap(data) {
        if (!data || !Array.isArray(data.grid) || !heightmapCtx) return;

        const grid = data.grid;
        const h = grid.length;
        const w = h ? grid[0].length : 0;
        if (!w || !h) return;

        // Adjust logical canvas size to grid
        if (heightmapCanvas.width !== w || heightmapCanvas.height !== h) {
            heightmapCanvas.width = w;
            heightmapCanvas.height = h;
        }

        const imgData = heightmapCtx.createImageData(w, h);
        let idx = 0;

        for (let y = 0; y < h; y++) {
            const row = grid[y];
            for (let x = 0; x < w; x++) {
                const cell = row[x] || { h: 0, type: null };
                let v = Number(cell.h ?? 0);
                if (!Number.isFinite(v)) v = 0;
                v = Math.max(0, Math.min(255, v)); // clamp to [0,255]

                // Simple grayscale based on height
                const r = v;
                const g = v;
                const b = v;
                const a = 255;

                imgData.data[idx++] = r;
                imgData.data[idx++] = g;
                imgData.data[idx++] = b;
                imgData.data[idx++] = a;
            }
        }

        heightmapCtx.putImageData(imgData, 0, 0);
    }

    function drawTilemap(data) {
        if (!data || !Array.isArray(data.grid) || !tilemapCtx) return;

        const grid = data.grid;
        const h = grid.length;
        const w = h ? grid[0].length : 0;
        if (!w || !h) return;

        // Adjust logical canvas size to grid
        if (tilemapCanvas.width !== w || tilemapCanvas.height !== h) {
            tilemapCanvas.width = w;
            tilemapCanvas.height = h;
        }

        const imgData = tilemapCtx.createImageData(w, h);
        let idx = 0;

        for (let y = 0; y < h; y++) {
            const row = grid[y];
            for (let x = 0; x < w; x++) {
                const cell = row[x] || { h: 0, type: null };
                const type = (cell.type || '').toLowerCase();
                
                let r=0, g=0, b=0;
                
                // Simple color mapping
                if (type.includes('water')) {
                    r = 0; g = 100; b = 200;
                } else if (type.includes('tree')) {
                    r = 34; g = 139; b = 34; // ForestGreen
                } else if (type.includes('rock') || type.includes('mountain')) {
                    r = 128; g = 128; b = 128;
                } else if (type.includes('land')) {
                    r = 210; g = 180; b = 140; // Tan
                } else {
                    // Fallback to grayscale heightmap if type is unknown/null
                    let v = Number(cell.h ?? 0);
                    if (!Number.isFinite(v)) v = 0;
                    v = Math.max(0, Math.min(255, v));
                    r = v; g = v; b = v;
                }

                imgData.data[idx++] = r;
                imgData.data[idx++] = g;
                imgData.data[idx++] = b;
                imgData.data[idx++] = 255;
            }
        }

        tilemapCtx.putImageData(imgData, 0, 0);
    }

    function openStream() {
        if (es) {
            try { es.close(); } catch(e) {}
            es = null;
        }

    setStatus('Connecting…');
        es = new EventSource(sseUrl);

        es.onopen = function () {
            reconnectAttempts = 0;
            setStatus('Connected');
        };

        es.onmessage = function (evt) {
            const line = evt.data || '';
            const cls = classifyLine(line);
            const step = detectStep(line);
            if (step) currentStepEl.textContent = step;

            // Update checklist based on log line
            updateStepChecklist(line);

            if (paused) {
                buffer.push({line, cls});
            } else {
                appendLine(line, cls);
            }
            const now = new Date();
            lastUpdateEl.textContent = 'Last update: ' + now.toLocaleString();
        };

        es.onerror = function (err) {
            setStatus('Disconnected — attempting reconnect');
            // automatic reconnect handled by EventSource, but attempt full reconnect if many failures
            reconnectAttempts++;
            if (reconnectAttempts > 5) {
                // close and attempt a programmatic reconnect with backoff
                try { es.close(); } catch(e) {}
                es = null;
                setTimeout(openStream, Math.min(60, reconnectAttempts * 2) * 1000);
            }
        };
    }

    pauseBtn.addEventListener('click', function () {
        paused = !paused;
        pauseBtn.textContent = paused ? 'Resume' : 'Pause';
        if (typer) {
            try {
                if (paused) typer.pause(); else typer.resume();
            } catch(e) { /* noop */ }
        }
        if (!paused && buffer.length) {
            buffer.forEach(item => appendLine(item.line, item.cls));
            buffer = [];
        }
    });

    clearBtn.addEventListener('click', function () {
        logEl.innerHTML = '';
        if (typer) { try { typer.destroy(); } catch(e) {} }
        // Recreate typer instance after clear
        if (window.TypeIt) {
            typer = new TypeIt('#log', {
                speed: 30,
                cursor: true,
                lifeLike: true,
                waitUntilVisible: false,
            }).go();
        }
        lineCount = 0;
        lineCountEl.textContent = 'Lines: 0';
    });

    reconnectBtn.addEventListener('click', function () {
        if (es) {
            try { es.close(); } catch(e) {}
            es = null;
        }
        openStream();
    });

    downloadBtn.addEventListener('click', function () {
        // Attempt to fetch the raw log via the same origin URL (may 404 if storage not web-accessible).
        const rawUrl = '/storage/logs/mapgen-' + encodeURIComponent(mapId) + '.log';
        // fallback: create a blob from current log contents
        fetch(rawUrl).then(resp => {
            if (!resp.ok) throw new Error('No raw log available');
            return resp.blob();
        }).then(blob => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'mapgen-' + mapId + '.log';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        }).catch(() => {
            // fallback to current in-memory log
            const text = Array.from(logEl.childNodes).map(n => n.textContent).join("\n");
            const blob = new Blob([text], {type: 'text/plain'});
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'mapgen-' + mapId + '-partial.log';
            document.body.appendChild(a);
            a.click();
            a.remove();
            URL.revokeObjectURL(url);
        });
    });

    copyBtn.addEventListener('click', function () {
        const text = Array.from(logEl.childNodes).map(n => n.textContent).join("\n");
        
        if (!text || text.trim().length === 0) {
            copyBtn.textContent = 'Nothing to copy';
            setTimeout(() => copyBtn.textContent = 'Copy to Clipboard', 1200);
            return;
        }

        // Try modern clipboard API first
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                const original = copyBtn.textContent;
                copyBtn.textContent = 'Copied!';
                setTimeout(() => copyBtn.textContent = original, 1200);
            }).catch(() => {
                copyBtn.textContent = 'Copy failed';
                setTimeout(() => copyBtn.textContent = 'Copy to Clipboard', 1500);
            });
        } else {
            // Fallback for older browsers
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.top = '-1000px';
            document.body.appendChild(ta);
            ta.focus();
            ta.select();
            try {
                document.execCommand('copy');
                const original = copyBtn.textContent;
                copyBtn.textContent = 'Copied!';
                setTimeout(() => copyBtn.textContent = original, 1200);
            } catch (e) {
                copyBtn.textContent = 'Copy failed';
                setTimeout(() => copyBtn.textContent = 'Copy to Clipboard', 1500);
            }
            document.body.removeChild(ta);
        }
    });

    // Start streaming when page loads
    openStream();

    // Initial heightmap draw
    fetchHeightmapData();

    // Optional: refresh every 5s while viewing this page
    setInterval(fetchHeightmapData, 5000);

    // Queue status polling
    const queueStatusUrl = "{{ route('game.queue.status', ['mapId' => $mapId]) }}";
    const queuePendingEl = document.getElementById('queuePending');
    const queueFailedEl = document.getElementById('queueFailed');
    const queueCurrentEl = document.getElementById('queueCurrent');
    
    function fetchQueueStatus() {
        fetch(queueStatusUrl)
            .then(resp => resp.json())
            .then(data => {
                if (data.ok) {
                    queuePendingEl.textContent = data.pending + ' pending';
                    queuePendingEl.style.color = data.pending > 0 ? '#fbbf24' : '#86efac';
                    
                    queueFailedEl.textContent = 'Failed: ' + data.failed;
                    queueFailedEl.style.color = data.failed > 0 ? '#fca5a5' : '#94a3b8';
                    
                    if (data.currentJob) {
                        const job = data.currentJob;
                        queueCurrentEl.textContent = `Current: ${job.displayName} (attempts: ${job.attempts})`;
                    } else if (data.recentFailure) {
                        queueCurrentEl.textContent = 'Last failure: ' + data.recentFailure.failed_at;
                        queueCurrentEl.style.color = '#fca5a5';
                    } else {
                        queueCurrentEl.textContent = 'No active jobs';
                        queueCurrentEl.style.color = '#94a3b8';
                    }
                }
            })
            .catch(err => {
                queuePendingEl.textContent = 'Error';
                queueFailedEl.textContent = '';
                queueCurrentEl.textContent = '';
            });
    }
    
    // Poll queue status every 2 seconds
    fetchQueueStatus();
    setInterval(fetchQueueStatus, 2000);

    // Clean up connection when navigating away
    window.addEventListener('beforeunload', function () {
        if (es) {
            try { es.close(); } catch(e) {}
            es = null;
        }
    });
})();
</script>
@endsection
