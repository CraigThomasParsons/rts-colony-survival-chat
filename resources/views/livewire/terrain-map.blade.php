<div class="p-4">
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <div class="text-sm text-gray-300">
                <span class="font-semibold">Terrain</span>
                <span class="text-gray-500">(tile viewport {{ $tileSize }}×{{ $tileSize }})</span>
                <span class="text-gray-500">offset</span>
                <span class="text-gray-200">({{ $offsetX }}, {{ $offsetY }})</span>
            </div>

            <div class="flex items-center gap-2 text-xs text-gray-300">
                <label class="text-gray-400">Size</label>
                <input type="number" min="8" max="128" step="1" wire:model.live="tileSize" class="w-20 bg-black/30 border border-gray-700 rounded px-2 py-1" />
                <button type="button" wire:click="pageUp" class="px-2 py-1 rounded border border-gray-700 hover:bg-gray-800">↑</button>
                <button type="button" wire:click="previousPage" class="px-2 py-1 rounded border border-gray-700 hover:bg-gray-800">←</button>
                <button type="button" wire:click="nextPage" class="px-2 py-1 rounded border border-gray-700 hover:bg-gray-800">→</button>
                <button type="button" wire:click="pageDown" class="px-2 py-1 rounded border border-gray-700 hover:bg-gray-800">↓</button>
            </div>
        </div>

        <div class="text-xs text-gray-300">
            Water <span class="text-blue-300">{{ $counts['water'] ?? 0 }}</span>
            · Mountains <span class="text-amber-300">{{ $counts['mountains'] ?? 0 }}</span>
            · Trees <span class="text-green-300">{{ $counts['trees'] ?? 0 }}</span>
            · Land <span class="text-gray-200">{{ $counts['land'] ?? 0 }}</span>
        </div>

        <div class="flex flex-col lg:flex-row gap-4">
            <div class="shrink-0 border border-gray-800 rounded bg-[#0b0b0b] p-3">
                <div class="text-sm text-gray-300 mb-2 flex items-center justify-between">
                    <span class="font-semibold">Minimap</span>
                    <button type="button" wire:click="$dispatch('terrain-map:refresh')" class="text-xs px-2 py-1 rounded border border-gray-700 hover:bg-gray-800">Refresh</button>
                </div>
                <canvas
                    x-data
                    x-init="(() => {
                        const mapId = @js($mapId);
                        const canvas = $el;
                        const ctx = canvas.getContext('2d');
                        const W = canvas.width;
                        const H = canvas.height;

                        function colorForType(type, hasTrees){
                            if (hasTrees || type === 29) return '#0b3a18';
                            if (type === 3 || (type != null && type >= 16 && type <= 27)) return '#003366';
                            if (type === 2 || (type != null && type >= 4 && type <= 15)) return '#6d6d6d';
                            return '#2E8A2E';
                        }

                        function clamp(v, lo, hi){ return Math.max(lo, Math.min(hi, v)); }

                        async function draw(){
                            // heightmap-data uses CELL coordinates; tiles are returned in the same CELL viewport
                            const url = `/game/${mapId}/heightmap-data?x=0&y=0&w=${W}&h=${H}&tiles=1`;
                            const res = await fetch(url, { cache: 'no-store' });
                            if (!res.ok) return;
                            const data = await res.json();
                            const tiles = data.tiles || [];
                            for (let y=0; y<H; y++){
                                const row = tiles[y] || [];
                                for (let x=0; x<W; x++){
                                    const t = row[x] || {};
                                    const type = t.type ?? null;
                                    const hasTrees = Boolean(t.has_trees ?? (type === 29));
                                    ctx.fillStyle = colorForType(type, hasTrees);
                                    ctx.fillRect(x, y, 1, 1);
                                }
                            }
                        }

                        let dragging = false;
                        function emitMove(evt){
                            const rect = canvas.getBoundingClientRect();
                            const nx = clamp((evt.clientX - rect.left) / rect.width, 0, 1);
                            const ny = clamp((evt.clientY - rect.top) / rect.height, 0, 1);

                            // canvas pixels represent CELLS; convert to TILES and center the viewport
                            const cellX = Math.floor(nx * W);
                            const cellY = Math.floor(ny * H);
                            const tileX = cellX * 2 - Math.floor(@js($tileSize) / 2);
                            const tileY = cellY * 2 - Math.floor(@js($tileSize) / 2);

                            Livewire.dispatch('terrain-map:move', { tileX, tileY });
                        }

                        canvas.addEventListener('mousedown', (e)=>{ dragging = true; emitMove(e); });
                        window.addEventListener('mousemove', (e)=>{ if (!dragging) return; emitMove(e); });
                        window.addEventListener('mouseup', ()=>{ dragging = false; });

                        // Initial draw
                        draw().catch(console.error);
                        // Re-draw when Offset/size changes (cheap + keeps the viewport context fresh)
                        Livewire.hook('message.processed', () => { draw().catch(()=>{}); });
                    })()"
                    width="200"
                    height="200"
                    style="image-rendering:pixelated;background:#090909;display:block;border-radius:8px;"
                ></canvas>
                <div class="text-[11px] text-gray-400 mt-2">Click/drag to move the 32×32 viewport</div>
            </div>

            <div class="overflow-auto border border-gray-800 rounded bg-[#0b0b0b] p-2">
            <style>
                /* Local, explicit colors so the Livewire editor doesn't depend on legacy inline mapgen styles. */
                .landTile { background-color:#2E8A2E; }
                .treeTile { background-color:#003300; border-bottom:3px solid #533118; }
                .waterTile { background-color:#003366; }
                .rockTile { background-color:#6d6d6d; }
            </style>
            <table class="border-collapse" style="table-layout: fixed;">
                <tbody>
                @foreach ($grid as $row)
                    <tr>
                        @foreach ($row as $tile)
                            <livewire:tile
                                :key="'tile-'.$mapId.'-'.$tile['map_x'].'-'.$tile['map_y']"
                                :tile="$tile"
                            />
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        </div>
    </div>
</div>
