@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white shadow-md rounded-lg p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-2xl font-bold">Map Generation Progress — Map #{{ $mapId }}</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Streaming log: <code class="bg-gray-100 px-1 rounded">storage/logs/mapgen-{{ $mapId }}.log</code>
                </p>
            </div>

            <div class="text-right space-y-2">
                <a href="{{ url('/Map/load/'.$mapId.'/') }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 text-sm">Open Map View</a>
                <a href="{{ route('main.entrance') }}" class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200 text-sm">Main Menu</a>
            </div>
        </div>

        <hr class="my-4">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="col-span-1 bg-gray-50 p-3 rounded">
                <div class="text-xs text-gray-500">Connection</div>
                <div id="connStatus" class="mt-1 text-sm font-medium">Connecting…</div>
                <div class="text-xs text-gray-400 mt-2" id="lastUpdate">Last update: —</div>
            </div>

            <div class="col-span-1 bg-gray-50 p-3 rounded">
                <div class="text-xs text-gray-500">Current Step</div>
                <div id="currentStep" class="mt-1 text-sm font-medium">—</div>
                <div class="text-xs text-gray-400 mt-2" id="lineCount">Lines: 0</div>
            </div>

            <div class="col-span-1 bg-gray-50 p-3 rounded flex flex-col">
                <div class="text-xs text-gray-500">Controls</div>

                <div class="mt-2 flex space-x-2">
                    <button id="pauseBtn" class="px-3 py-2 bg-yellow-400 rounded text-sm">Pause</button>
                    <button id="clearBtn" class="px-3 py-2 bg-gray-200 rounded text-sm">Clear</button>
                    <button id="downloadBtn" class="px-3 py-2 bg-green-600 text-white rounded text-sm">Download</button>
                    <button id="reconnectBtn" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Reconnect</button>
                </div>

                <div class="mt-3 text-xs text-gray-500">
                    Auto-scroll:
                    <label class="inline-flex items-center ml-2">
                        <input id="autoScroll" type="checkbox" checked class="form-checkbox" />
                    </label>
                </div>
            </div>
        </div>

        <div class="bg-black text-white rounded p-2">
            <pre id="log" class="whitespace-pre-wrap max-h-[60vh] overflow-y-auto text-sm leading-tight font-mono"></pre>
        </div>
    </div>
</div>

<script>
(function () {
    const mapId = @json($mapId);
    const sseUrl = "{{ route('game.mapgen.progress.stream', ['mapId' => $mapId]) }}";
    const logEl = document.getElementById('log');
    const connStatusEl = document.getElementById('connStatus');
    const lastUpdateEl = document.getElementById('lastUpdate');
    const currentStepEl = document.getElementById('currentStep');
    const lineCountEl = document.getElementById('lineCount');
    const pauseBtn = document.getElementById('pauseBtn');
    const clearBtn = document.getElementById('clearBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const reconnectBtn = document.getElementById('reconnectBtn');
    const autoScrollEl = document.getElementById('autoScroll');

    let es = null;
    let paused = false;
    let lineCount = 0;
    let buffer = [];
    let reconnectAttempts = 0;

    function setStatus(text, cls) {
        connStatusEl.textContent = text;
        connStatusEl.className = cls ? cls + " mt-1 text-sm font-medium" : "mt-1 text-sm font-medium";
    }

    function appendLine(line, metaClass = '') {
        lineCount++;
        lineCountEl.textContent = 'Lines: ' + lineCount;

        const node = document.createElement('div');
        node.textContent = line;
        if (metaClass) node.classList.add(metaClass);

        logEl.appendChild(node);

        if (autoScrollEl.checked) {
            logEl.scrollTop = logEl.scrollHeight;
        }
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

    function classifyLine(line) {
        const l = line.toLowerCase();
        if (l.match(/error|exception|fatal|failed/)) return 'text-red-400';
        if (l.match(/warning|warn/)) return 'text-yellow-300';
        if (l.match(/completed|finished|success/)) return 'text-green-300';
        return '';
    }

    function openStream() {
        if (es) {
            try { es.close(); } catch(e) {}
            es = null;
        }

        setStatus('Connecting…', 'text-gray-700');
        es = new EventSource(sseUrl);

        es.onopen = function () {
            reconnectAttempts = 0;
            setStatus('Connected', 'text-green-600');
        };

        es.onmessage = function (evt) {
            const line = evt.data || '';
            const cls = classifyLine(line);
            const step = detectStep(line);
            if (step) currentStepEl.textContent = step;

            if (paused) {
                buffer.push({line, cls});
            } else {
                appendLine(line, cls);
            }
            const now = new Date();
            lastUpdateEl.textContent = 'Last update: ' + now.toLocaleString();
        };

        es.onerror = function (err) {
            setStatus('Disconnected — attempting reconnect', 'text-red-500');
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
        pauseBtn.classList.toggle('bg-yellow-400', paused);
        pauseBtn.classList.toggle('bg-green-600', !paused);
        if (!paused && buffer.length) {
            buffer.forEach(item => appendLine(item.line, item.cls));
            buffer = [];
        }
    });

    clearBtn.addEventListener('click', function () {
        logEl.innerHTML = '';
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

    // Start streaming when page loads
    openStream();

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
