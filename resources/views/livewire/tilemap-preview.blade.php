<div style="position:relative; width:100%; height:100vh; background:#0b0b0b; overflow:hidden;">
    <!-- Simple HTML 32x32 map grid -->
    <div id="htmlGrid" style="position:relative; z-index:2; padding:12px;">
        <div style="color:#ddd; margin-bottom:6px;">HTML Map (32×32)</div>
        <div id="gridContainer" style="display:grid; grid-template-columns: repeat(32, 16px); grid-auto-rows: 16px; gap:1px; background:#111; padding:4px; border:1px solid #333; border-radius:6px; width:max-content;">
            <!-- cells injected by JS -->
        </div>
    </div>
    <!-- Main world canvas fills window -->
    <canvas id="tilemapCanvas" style="position:absolute; inset:0; width:100%; height:100%; image-rendering: pixelated; background:#000;"></canvas>

    <!-- Hover debug overlay -->
    <div id="hoverInfo" style="position:absolute; left:16px; top:16px; background:rgba(0,0,0,0.65); border:1px solid #333; padding:8px 10px; border-radius:6px; color:#e5e5e5; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace; font-size:12px; line-height:1.35; min-width:220px;">
        <div style="color:#9ca3af">Hover a tile…</div>
    </div>

    <!-- Minimap overlay in bottom-right -->
    <div style="position:absolute; right:16px; top:16px; background:#111; border:1px solid #333; padding:8px; border-radius:6px;">
        <div class="flex items-center justify-between mb-2">
            <span class="text-gray-300 text-sm">Minimap</span>
            <input id="zoom" type="range" min="1" max="8" step="1" value="2" title="Zoom" />
        </div>
        <canvas id="minimapCanvas" width="200" height="200" style="image-rendering: pixelated; background:#090909; display:block;"></canvas>
        <div class="text-gray-400 text-xs mt-2">Drag on minimap to move camera</div>
        <div id="counts" class="text-gray-300 text-xs mt-2">Counts: <span class="text-gray-500">loading…</span></div>
    </div>

    <!-- Optional navbar space above (reserved by layout) -->
</div>

    <script>
    (function(){
        const mapId = @json($mapId);
        const sizeX = @json($sizeX);
        const sizeY = @json($sizeY);
    // Fetch a 32x32 slice in CELL space starting from (0,0)
    async function renderHtmlGrid(){
        try {
            const url = `/game/${mapId}/heightmap-data?x=0&y=0&w=32&h=32&tiles=1`;
            const res = await fetch(url, { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            const grid = data.grid || [];
            const tiles = data.tiles || [];
            const cont = document.getElementById('gridContainer');
            if (!cont) return;
            cont.innerHTML = '';
            // Helpers for color mapping
            function colorForType(type){
                if (type === 1 || type == null) return '#228B22';
                if (type === 2) return '#6d6d6d';
                if (type === 3) return '#1e5aa8';
                if (type >= 4 && type <= 15) return '#aa8844';
                if (type === 29) return '#2f6f31';
                return '#222222';
            }
            function grayForHeight(h){
                const v = Math.max(0, Math.min(255, Number(h||0)));
                return `rgb(${v},${v},${v})`;
            }
            for (let y=0; y<32; y++){
                const grow = grid[y] || [];
                const trow = tiles[y] || [];
                for (let x=0; x<32; x++){
                    const cell = grow[x] || {h:0};
                    const t = trow[x] || {};
                    const type = t.type ?? t.tileTypeId ?? null;
                    const div = document.createElement('div');
                    div.style.width = '16px';
                    div.style.height = '16px';
                    div.style.boxSizing = 'border-box';
                    div.title = `(${x},${y}) h=${cell.h ?? 0} type=${type ?? '-'}\n`;
                    div.style.background = (type != null) ? colorForType(type) : grayForHeight(cell.h);
                    cont.appendChild(div);
                }
            }
        } catch(e) {
            console.warn('HTML grid render failed', e);
        }
    }
    const canvas = document.getElementById('tilemapCanvas');
    const ctx = canvas.getContext('2d');
    const zoomEl = document.getElementById('zoom');
    const minimap = document.getElementById('minimapCanvas');
    const miniCtx = minimap.getContext('2d');
    const hoverEl = document.getElementById('hoverInfo');

        // Logical tile grid is 2x cells in both axes (4 tiles per cell)
    const tilesX = sizeX * 2;
        const tilesY = sizeY * 2;
        // Ensure the backing buffer is aligned whatever the layout is doing.
        // (CSS keeps canvas filling container; we set internal buffer sizes per viewport.)
        window.addEventListener('resize', () => {
            clampCam();
            loadData().catch(console.error);
        });

        // Viewport (camera) state in tile coords
        let zoom = parseInt(zoomEl.value, 10);
        function viewPixelW(){ return canvas.getBoundingClientRect().width; }
        function viewPixelH(){ return canvas.getBoundingClientRect().height; }
        function tilesVisibleX(){ return Math.max(1, Math.floor(viewPixelW() / zoom)); }
        function tilesVisibleY(){ return Math.max(1, Math.floor(viewPixelH() / zoom)); }
    let camX = 0; // top-left tile x of viewport
    let camY = 0; // top-left tile y of viewport
        function setCanvasBuffer(){
            canvas.width = tilesVisibleX();
            canvas.height = tilesVisibleY();
        }
        setCanvasBuffer();

        function colorForType(type){
            // Palette matched to existing blade css helper (resources/views/mapgen/tiletypeclassname.blade.php)
            // plus a few extra visually distinct picks.
            if (type === 1 || type == null) return '#228B22'; // passable land (green)
            if (type === 2) return '#6d6d6d'; // rocky / impassable rocks (grey)
            if (type === 3) return '#1e5aa8'; // water (blue)
            // Mountain ridge/corners/edges (4-15)
            if (type >= 4 && type <= 15) return '#aa8844'; // tan/brown
            // Trees / forest (commonly 29 in other overlay config)
            if (type === 29) return '#2f6f31';
            return '#222222'; // unknown
        }

        // Grid is intentionally removed (user request).

        // Last fetched cell-space tiles (for hover debug)
        let lastCellX = 0, lastCellY = 0;
        let lastCellTiles = [];

        function classifyCellHeight(h, thresholds){
            const waterMax = thresholds.waterMax ?? 22;
            const low = thresholds.low ?? 163;
            const high = thresholds.high ?? 203;
            if (h <= waterMax) return `water (<=${waterMax})`;
            if (h >= high) return `peaks (>=${high})`;
            if (h >= low) return `foothills (>=${low})`;
            return 'land';
        }

        async function loadData(){
            // Reuse heightmap-data endpoint which includes tiles with types
            // Request only the viewport rectangle and tiles array
            const vx = tilesVisibleX();
            const vy = tilesVisibleY();
            // NOTE: heightmap-data slicing uses CELL coordinates, not TILE coordinates.
            // For tilemap preview we must convert tile coords -> cell coords.
            const cellX = Math.floor(camX / 2);
            const cellY = Math.floor(camY / 2);
            const cellW = Math.ceil(vx / 2);
            const cellH = Math.ceil(vy / 2);
            const url = `/game/${mapId}/heightmap-data?x=${cellX}&y=${cellY}&w=${cellW}&h=${cellH}&tiles=1`;
            const res = await fetch(url);
            const data = await res.json();
            // data.tiles is indexed by CELL coords (within the requested cell viewport)
            const cellTiles = data.tiles || [];
            // data.grid is the height array in the same cell viewport
            const cellGrid = data.grid || [];
            const thresholds = {
                // Prefer server-provided thresholds when available
                waterMax: (data.thresholds && data.thresholds.waterMax) ?? 22,
                low:      (data.thresholds && data.thresholds.foothills) ?? (data.thresholds && data.thresholds.low) ?? 163,
                high:     (data.thresholds && data.thresholds.peaks) ?? (data.thresholds && data.thresholds.high) ?? 203,
            };

            lastCellX = cellX;
            lastCellY = cellY;
            lastCellTiles = cellTiles;

            // Update counts (water, mountains, trees) by scanning returned tile types
            try {
                const countsEl = document.getElementById('counts');
                if (countsEl) {
                    let water=0, mountains=0, trees=0;
                    for (let yy=0; yy<cellTiles.length; yy++) {
                        const row = cellTiles[yy] || [];
                        for (let xx=0; xx<row.length; xx++) {
                            const t = row[xx] || {};
                            const type = t.type ?? t.tileTypeId ?? null;
                            if (type === 3) water++;
                            else if (type === 29) trees++;
                            else if (type != null && type >= 4 && type <= 15) mountains++;
                        }
                    }
                    countsEl.innerHTML = `Water <span class="text-blue-300">${water}</span> · Mountains <span class="text-amber-300">${mountains}</span> · Trees <span class="text-green-300">${trees}</span>`;
                }
                const topCountsEl = document.getElementById('tileCounts');
                if (topCountsEl) {
                    // Reflect same counts near page title (if present)
                    const txt = topCountsEl.textContent || '';
                    topCountsEl.innerHTML = `Counts: Water <span class="text-blue-300">${water}</span> · Mountains <span class="text-amber-300">${mountains}</span> · Trees <span class="text-green-300">${trees}</span>`;
                }
            } catch(e) { /* noop */ }
            // Render only the viewport area
            function renderViewport(){
                const vx = tilesVisibleX();
                const vy = tilesVisibleY();
                setCanvasBuffer();
                for(let yy=0; yy<vy; yy++){
                    const tileY = camY + yy;
                    // Convert tile->cell, then offset within the returned cell viewport
                    const cy = Math.floor(tileY / 2) - cellY;
                    const row = cellTiles[cy] || [];
                    for(let xx=0; xx<vx; xx++){
                        const tileX = camX + xx;
                        const cx = Math.floor(tileX / 2) - cellX;
                        const t = row[cx] || { type: 1 };
                        ctx.fillStyle = colorForType(t.type ?? t.tileTypeId ?? 1);
                        ctx.fillRect(xx, yy, 1, 1);
                    }
                }
                // Scale up visually by CSS using zoom
                // Keep the canvas filling the container; apply zoom using image-rendered scaling.
                // We do this by setting the backing buffer to the viewport amount and letting CSS stretch.
                canvas.style.width = '100%';
                canvas.style.height = '100%';
                renderMinimap();
            }
            renderViewport();

            function clampCam(){
                const vx = tilesVisibleX();
                const vy = tilesVisibleY();
                camX = Math.max(0, Math.min(tilesX - vx, camX));
                camY = Math.max(0, Math.min(tilesY - vy, camY));
            }

            // Hover debug: show tile + cell info under cursor
            function updateHover(e){
                const rect = canvas.getBoundingClientRect();
                const nx = (e.clientX - rect.left) / rect.width;
                const ny = (e.clientY - rect.top) / rect.height;

                const vx = tilesVisibleX();
                const vy = tilesVisibleY();
                const localTileX = Math.max(0, Math.min(vx - 1, Math.floor(nx * vx)));
                const localTileY = Math.max(0, Math.min(vy - 1, Math.floor(ny * vy)));
                const tileX = camX + localTileX;
                const tileY = camY + localTileY;
                const cx = Math.floor(tileX / 2);
                const cy = Math.floor(tileY / 2);

                const localCellX = cx - lastCellX;
                const localCellY = cy - lastCellY;

                const tRow = lastCellTiles[localCellY] || [];
                const t = tRow[localCellX] || null;
                const type = t?.type ?? t?.tileTypeId ?? null;

                const gRow = cellGrid[localCellY] || [];
                const h = (gRow[localCellX] && typeof gRow[localCellX].h === 'number') ? gRow[localCellX].h : (gRow[localCellX]?.h ?? null);
                const heightVal = (h === null || h === undefined) ? '-' : h;
                const band = (h === null || h === undefined) ? '-' : classifyCellHeight(h, thresholds);

                hoverEl.innerHTML = `
                    <div><span style="color:#9ca3af">tile</span> (${tileX}, ${tileY})</div>
                    <div><span style="color:#9ca3af">cell</span> (${cx}, ${cy})</div>
                    <div><span style="color:#9ca3af">tileType_id</span> ${type ?? '-'}</div>
                    <div><span style="color:#9ca3af">height</span> ${heightVal}</div>
                    <div><span style="color:#9ca3af">band</span> ${band}</div>
                `;
            }

            canvas.onmousemove = updateHover;
            canvas.onmouseleave = () => {
                hoverEl.innerHTML = '<div style="color:#9ca3af">Hover a tile…</div>';
            };

            // Cap zoom so viewport stays within safe limits
            const MAX_TILES_BUFFER = 1024; // max internal buffer dimension
            zoomEl.oninput = () => {
                const desired = parseInt(zoomEl.value, 10);
                const maxZoomX = Math.floor(viewPixelW() / Math.min(MAX_TILES_BUFFER, tilesX));
                const maxZoomY = Math.floor(viewPixelH() / Math.min(MAX_TILES_BUFFER, tilesY));
                const maxZoom = Math.max(1, Math.min(desired, Math.min(maxZoomX, maxZoomY)));
                zoom = maxZoom;
                clampCam();
                // Re-fetch tiles for new viewport size
                loadData().catch(console.error);
            };

            // Re-fetch tiles when panning via minimap
            minimap.addEventListener('mousedown', ()=>{
                // handled in drag logic below; we will call loadData()
            });

            // Minimap rendering with viewport rectangle and drag-to-move
            function renderMinimap(){
                const mw = minimap.width, mh = minimap.height;
                const sx = tilesX / mw, sy = tilesY / mh;
                // For minimap, fetch a low-res *CELL* tile snapshot (already in cell coords)
                // We reuse the latest `cellTiles` for now, which represents only the current cell viewport.
                // Fill everything unknown as land so minimap remains responsive.
                // Draw minimap background
                for(let my=0; my<mh; my++){
                    const tileY = Math.floor(my * sy);
                    const cy = Math.floor(tileY / 2) - cellY;
                    const row = cellTiles[cy] || [];
                    for(let mx=0; mx<mw; mx++){
                        const tileX = Math.floor(mx * sx);
                        const cx = Math.floor(tileX / 2) - cellX;
                        const t = row[cx] || { type: 1 };
                        miniCtx.fillStyle = colorForType(t.type ?? t.tileTypeId ?? 1);
                        miniCtx.fillRect(mx, my, 1, 1);
                    }
                }
                // Draw viewport rectangle
                const vx = tilesVisibleX(), vy = tilesVisibleY();
                miniCtx.strokeStyle = '#00ffff';
                miniCtx.lineWidth = 1;
                miniCtx.strokeRect(Math.floor(camX / sx), Math.floor(camY / sy), Math.ceil(vx / sx), Math.ceil(vy / sy));
            }

            let dragging = false;
            minimap.addEventListener('mousedown', (e)=>{
                dragging = true;
                const rect = minimap.getBoundingClientRect();
                const mx = e.clientX - rect.left; const my = e.clientY - rect.top;
                const sx = tilesX / minimap.width, sy = tilesY / minimap.height;
                camX = Math.floor(mx * sx - tilesVisibleX()/2);
                camY = Math.floor(my * sy - tilesVisibleY()/2);
                clampCam(); loadData().catch(console.error);
            });
            window.addEventListener('mousemove', (e)=>{
                if(!dragging) return;
                const rect = minimap.getBoundingClientRect();
                const mx = e.clientX - rect.left; const my = e.clientY - rect.top;
                const sx = tilesX / minimap.width, sy = tilesY / minimap.height;
                camX = Math.floor(mx * sx - tilesVisibleX()/2);
                camY = Math.floor(my * sy - tilesVisibleY()/2);
                clampCam(); loadData().catch(console.error);
            });
            window.addEventListener('mouseup', ()=>{ dragging = false; });
        }

        // First, render the simple HTML 32x32 map
        renderHtmlGrid().then(()=>{
            // Then render the canvas viewport as before
            loadData().catch(console.error);
        });
    })();
    </script>
</div>
