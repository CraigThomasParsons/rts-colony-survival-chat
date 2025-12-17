(function () {
  try {
    // Read config from meta tag to avoid Blade parsing issues
    const configMeta = document.querySelector('meta[name="map-config"]');
    if (!configMeta) {
      console.error('Map configuration meta tag not found');
      return;
    }
    
    const config = JSON.parse(configMeta.getAttribute('content'));
    const mapId = config.mapId;
    const sseUrl = config.sseUrl;

    if (!mapId || !sseUrl) {
      console.error('Map generation config not found');
      return;
    }

    const logEl = document.getElementById('log');
    const connStatusEl = document.getElementById('connStatus');
    const lastUpdateEl = document.getElementById('lastUpdate');
    const currentStepEl = document.getElementById('currentStep');
    const lineCountEl = document.getElementById('lineCount');
    const pauseBtn = document.getElementById('pauseBtn');
    const clearBtn = document.getElementById('clearBtn');
    const downloadBtn = document.getElementById('downloadBtn');
    const copyBtn = document.getElementById('copyBtn');
    const reconnectBtn = document.getElementById('reconnectBtn');
    const autoScrollEl = document.getElementById('autoScroll');

    let es = null;
    let paused = false;
    let lineCount = 0;
    let buffer = [];
    let reconnectAttempts = 0;

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
      } catch(e) {}
    }
    document.addEventListener('DOMContentLoaded', initTyper);
    window.addEventListener('load', initTyper);

    function setStatus(text, cls) {
      if (!connStatusEl) return;
      connStatusEl.textContent = text;
      connStatusEl.className = cls ? cls + " mt-1 text-sm font-medium" : "mt-1 text-sm font-medium";
    }

    function appendLine(line, metaClass) {
      lineCount++;
      if (lineCountEl) lineCountEl.textContent = 'Lines: ' + lineCount;

      if (!typer || !logEl) {
        initTyper();
        if (logEl) {
          const node = document.createElement('div');
          node.textContent = line;
          if (metaClass) node.classList.add(metaClass);
          logEl.appendChild(node);
          if (autoScrollEl && autoScrollEl.checked) {
            logEl.scrollTop = logEl.scrollHeight;
          }
        }
        return;
      }
      const safeLine = line.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      const prefix = metaClass ? '<span class="' + metaClass + '">' : '';
      const suffix = metaClass ? '</span>' : '';
      typer
        .type(prefix + safeLine + suffix)
        .break()
        .exec(() => { if (autoScrollEl && autoScrollEl.checked) { logEl.scrollTop = logEl.scrollHeight; } });
    }

    function detectStep(line) {
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
      if (es) { try { es.close(); } catch(e) {} es = null; }
      setStatus('Connecting…');
      es = new EventSource(sseUrl);
      es.onopen = function () { reconnectAttempts = 0; setStatus('Connected'); };
      es.onmessage = function (evt) {
        const line = evt.data || '';
        const cls = classifyLine(line);
        const step = detectStep(line);
        if (step && currentStepEl) currentStepEl.textContent = step;
        if (paused) { buffer.push({line, cls}); } else { appendLine(line, cls); }
        const now = new Date();
        if (lastUpdateEl) lastUpdateEl.textContent = 'Last update: ' + now.toLocaleString();
      };
      es.onerror = function () {
        setStatus('Disconnected — attempting reconnect');
        reconnectAttempts++;
        if (reconnectAttempts > 5) {
          try { es.close(); } catch(e) {}
          es = null;
          setTimeout(openStream, Math.min(60, reconnectAttempts * 2) * 1000);
        }
      };
    }

    if (pauseBtn) pauseBtn.addEventListener('click', function () {
      paused = !paused;
      pauseBtn.textContent = paused ? 'Resume' : 'Pause';
      if (typer) { try { if (paused) typer.pause(); else typer.resume(); } catch(e) {} }
      if (!paused && buffer.length) { buffer.forEach(item => appendLine(item.line, item.cls)); buffer = []; }
    });

    if (clearBtn) clearBtn.addEventListener('click', function () {
      if (logEl) logEl.innerHTML = '';
      if (typer) { try { typer.destroy(); } catch(e) {} }
      if (window.TypeIt && logEl) {
        typer = new TypeIt('#log', { speed: 30, cursor: true, lifeLike: true, waitUntilVisible: false }).go();
      }
      lineCount = 0;
      if (lineCountEl) lineCountEl.textContent = 'Lines: 0';
    });

    if (reconnectBtn) reconnectBtn.addEventListener('click', function () {
      if (es) { try { es.close(); } catch(e) {} es = null; }
      openStream();
    });

    if (downloadBtn) downloadBtn.addEventListener('click', function () {
      const rawUrl = '/storage/logs/mapgen-' + encodeURIComponent(mapId) + '.log';
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
        const text = logEl ? Array.from(logEl.childNodes).map(n => n.textContent).join("\n") : '';
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

    if (copyBtn) copyBtn.addEventListener('click', async function () {
      try {
        const text = logEl ? Array.from(logEl.childNodes).map(n => n.textContent).join("\n") : '';
        if (!text || text.trim().length === 0) {
          copyBtn.textContent = 'Nothing to copy';
          setTimeout(() => copyBtn.textContent = 'Copy to Clipboard', 1200);
          return;
        }
        if (navigator.clipboard && navigator.clipboard.writeText) {
          await navigator.clipboard.writeText(text);
        } else {
          const ta = document.createElement('textarea');
          ta.value = text;
          ta.style.position = 'fixed';
          ta.style.top = '-1000px';
          document.body.appendChild(ta);
          ta.focus();
          ta.select();
          document.execCommand('copy');
          document.body.removeChild(ta);
        }
        const original = copyBtn.textContent;
        copyBtn.textContent = 'Copied!';
        copyBtn.classList.add('btn-success');
        setTimeout(() => { copyBtn.textContent = original; copyBtn.classList.remove('btn-success'); }, 1200);
      } catch (e) {
        const original = copyBtn.textContent;
        copyBtn.textContent = 'Copy failed';
        setTimeout(() => copyBtn.textContent = original, 1500);
      }
    });

    openStream();
    window.addEventListener('beforeunload', function () { if (es) { try { es.close(); } catch(e) {} es = null; } });
  } catch (e) {
    // swallow errors to avoid breaking Blade render
  }
})();
