<?php $__env->startSection('content'); ?>
<link rel="stylesheet" href="<?php echo e(asset('css/panel.css')); ?>">
<script src="https://cdn.jsdelivr.net/npm/typeit@8.8.3/dist/typeit.min.js" defer></script>

<div class="panel">
    <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:1rem;">
        <div>
            <h1>Map Generation Progress — Map #<?php echo e($mapId); ?></h1>
            <div class="muted" style="font-size:0.85rem;">
                Streaming log: <code>storage/logs/mapgen-<?php echo e($mapId); ?>.log</code>
            </div>
        </div>

        <div style="text-align:right; display:flex; gap:0.5rem;">
            <a href="<?php echo e(url('/Map/load/'.$mapId.'/')); ?>" class="btn btn-primary">Open Map View</a>
            <a href="<?php echo e(route('control-panel')); ?>" class="btn btn-muted">Control Panel</a>
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

<script>
(function () {
    const mapId = <?php echo json_encode($mapId, 15, 512) ?>;
    const sseUrl = "<?php echo e(route('game.mapgen.progress.stream', ['mapId' => $mapId])); ?>";
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

    // Clean up connection when navigating away
    window.addEventListener('beforeunload', function () {
        if (es) {
            try { es.close(); } catch(e) {}
            es = null;
        }
    });
})();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/game/progress.blade.php ENDPATH**/ ?>