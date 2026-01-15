// Feudal Frontiers game bootstrap
// Exports a function to start a Phaser game on demand from Blade/Livewire.

import Phaser from 'phaser';
import { registerPhaserAssets, postLoadFallback, createWorkerAnimations } from './assets';

export function startFeudalFrontiersGame({
    mountId = 'phaser-game',
    width = 512,
    height = 512,
    backgroundColor = '#2d2d2d',
    debug = false,
    workerFrames = 4,
    idleFrameRate = 6,
    frameWidth = 64,
    frameHeight = 64,
    rows = { idle: 0, north: 1, south: 2, east: 3, west: 4 },
    camera = { zoom: 1, minZoom: 0.5, maxZoom: 2, drag: true },
    moveSpeed = 80,
    rightClickQueue = true,
    // Tile overlay config: expects a callback that returns a 2D array of tile objects or simple ints
    tileOverlay = { enabled: false, fetch: null, cellSize: 32, colorMap: { 1: 0x3b8d2a, 3: 0x1e5aa8, 29: 0x2f6f31, 2: 0x6d6d6d } },
    // Map status polling config
    statusPolling = { enabled: false, url: null, intervalMs: 4000, onUpdate: null }
} = {}) {
    const mountEl = document.getElementById(mountId);
    if (!mountEl) {
        console.warn(`FeudalFrontiers: mount element '#${mountId}' not found.`);
        return null;
    }

    const sceneDefinition = {
        preload() {
            registerPhaserAssets(this, { workerFrames, frameWidth, frameHeight });
        },
        create() {
            postLoadFallback(this, { frameWidth, frameHeight });
            createWorkerAnimations(this, { idleFrameRate, workerFrames, rows });
            // Simple demo placement.
            if (this.textures.exists('grass')) {
                // Fill a small grid
                for (let y = 0; y < 4; y++) {
                    for (let x = 0; x < 4; x++) {
                        this.add.image(64 + x * 64, 64 + y * 64, 'grass');
                    }
                }
            }
            if (this.textures.exists('worker')) {
                const worker = this.add.sprite(160, 160, 'worker');
                worker.play('worker-idle');
                worker.setInteractive();
                // Movement state
                this.workerState = {
                    sprite: worker,
                    target: null,
                    lastAnim: 'worker-idle',
                    queue: []
                };
                // Click-to-move left button
                this.input.on('pointerdown', (pointer) => {
                    if (pointer.button !== 0) return;
                    const cam = this.cameras.main;
                    const worldX = cam.scrollX + pointer.x / cam.zoom;
                    const worldY = cam.scrollY + pointer.y / cam.zoom;
                    // Replace current target and reset queue
                    this.workerState.queue = [];
                    this.workerState.target = { x: worldX, y: worldY };
                    this._placeOrMoveTargetMarker(worldX, worldY);
                });
                if (rightClickQueue) {
                    // Append waypoint on right-click
                    this.input.on('pointerdown', (pointer) => {
                        if (pointer.button !== 2) return; // right-click
                        const cam = this.cameras.main;
                        const worldX = cam.scrollX + pointer.x / cam.zoom;
                        const worldY = cam.scrollY + pointer.y / cam.zoom;
                        this.workerState.queue.push({ x: worldX, y: worldY });
                        this._drawWaypointMarkers();
                        // If idle (no active target) start processing next
                        if (!this.workerState.target) {
                            this.workerState.target = this.workerState.queue.shift();
                            this._placeOrMoveTargetMarker(this.workerState.target.x, this.workerState.target.y);
                        }
                    });
                    // Prevent browser context menu on canvas parent element
                    this.game.canvas.oncontextmenu = (e) => e.preventDefault();
                }
                // Highlight worker on direct click
                this.input.on('gameobjectdown', (_pointer, gameObject) => {
                    if (gameObject === worker) {
                        gameObject.setTint(0xffaa00);
                        setTimeout(() => gameObject.clearTint(), 300);
                    }
                });
            }

            // Target marker helper
            this._placeOrMoveTargetMarker = (x, y) => {
                if (!this._targetMarker) this._targetMarker = this.add.graphics();
                this._targetMarker.clear();
                this._targetMarker.lineStyle(2, 0xffd200, 1);
                this._targetMarker.strokeCircle(x, y, 10);
                this._targetMarker.lineStyle(1, 0xffd200, 0.4);
                this._targetMarker.strokeCircle(x, y, 18);
            };
            // Waypoint markers
            this._drawWaypointMarkers = () => {
                if (!this._waypointLayer) this._waypointLayer = this.add.graphics();
                this._waypointLayer.clear();
                this._waypointLayer.lineStyle(1, 0x66ddff, 0.9);
                if (this.workerState) {
                    this.workerState.queue.forEach((wp, idx) => {
                        this._waypointLayer.strokeCircle(wp.x, wp.y, 6);
                        this._waypointLayer.strokeCircle(wp.x, wp.y, 12);
                        this._waypointLayer.lineStyle(1, 0x66ddff, 0.5);
                        this._waypointLayer.strokeCircle(wp.x, wp.y, 18);
                    });
                }
            };
            // Animation switch helper
            this._setWorkerAnim = (key) => {
                if (!this.workerState) return;
                if (this.workerState.lastAnim === key) return;
                const { sprite } = this.workerState;
                sprite.play(key, true);
                this.workerState.lastAnim = key;
            };

            // Camera pan/zoom controls
            const cam = this.cameras.main;
            cam.setZoom(camera.zoom);
            cam.zoom = Phaser.Math.Clamp(cam.zoom, camera.minZoom, camera.maxZoom);
            if (camera.drag) {
                this.input.on('pointerdown', (pointer) => {
                    this._dragging = true;
                    this._dragStart = { x: pointer.x, y: pointer.y };
                    this._camStart = { x: cam.scrollX, y: cam.scrollY };
                });
                this.input.on('pointerup', () => { this._dragging = false; });
                this.input.on('pointermove', (pointer) => {
                    if (this._dragging) {
                        const dx = (pointer.x - this._dragStart.x) / cam.zoom;
                        const dy = (pointer.y - this._dragStart.y) / cam.zoom;
                        cam.scrollX = this._camStart.x - dx;
                        cam.scrollY = this._camStart.y - dy;
                    }
                });
            }
            this.input.on('wheel', (_pointer, _over, deltaX, deltaY) => {
                const sign = Math.sign(deltaY);
                const newZoom = Phaser.Math.Clamp(cam.zoom - sign * 0.1, camera.minZoom, camera.maxZoom);
                cam.setZoom(newZoom);
            });
            this.add.text(10, 10, 'Feudal Frontiers', { fontFamily: 'monospace', fontSize: '16px', color: '#ffffff' });

            // --------------------------------------------------
            // Tile overlay layer (simple colored rect grid)
            // --------------------------------------------------
            if (tileOverlay.enabled && typeof tileOverlay.fetch === 'function') {
                try {
                    const raw = tileOverlay.fetch();
                    // raw can be array or promise
                    const handleTiles = (grid) => {
                        if (!Array.isArray(grid)) return;
                        const g = this.add.graphics();
                        g.setDepth(5);
                        const size = tileOverlay.cellSize;
                        for (let y = 0; y < grid.length; y++) {
                            const row = grid[y];
                            if (!row) continue;
                            for (let x = 0; x < row.length; x++) {
                                const tile = row[x];
                                if (tile == null) continue;
                                // tileType detection: allow numeric or object
                                let typeId = typeof tile === 'number' ? tile : (tile.tileType_id ?? tile.tileTypeId ?? tile.type ?? tile.id);
                                if (typeId == null) typeId = 0;
                                const color = tileOverlay.colorMap[typeId] ?? 0x222222;
                                g.fillStyle(color, 0.38);
                                g.fillRect(x * size, y * size, size, size);
                            }
                        }
                        this._tileOverlayLayer = g;
                    };
                    const result = raw;
                    if (result && typeof result.then === 'function') {
                        result.then(handleTiles).catch(err => console.warn('Tile overlay load failed', err));
                    } else {
                        handleTiles(result);
                    }
                } catch (err) {
                    console.warn('Tile overlay init error', err);
                }
            }

            // --------------------------------------------------
            // Map status polling
            // --------------------------------------------------
            if (statusPolling.enabled && statusPolling.url) {
                const applyStatus = (data) => {
                    if (!data) return;
                    if (!this._statusText) {
                        this._statusText = this.add.text(10, 30, 'Map: ...', { fontFamily: 'monospace', fontSize: '14px', color: '#cccccc' });
                        this._statusText.setDepth(10);
                    }
                    const stateStr = data.state ?? data.map_state ?? 'Unknown';
                    const running = data.is_generating === true || data.locked === true;
                    this._statusText.setText(`Map State: ${stateStr}${running ? ' (generating...)' : ''}`);
                    if (typeof statusPolling.onUpdate === 'function') {
                        try { statusPolling.onUpdate(data); } catch {}
                    }
                };
                const poll = () => {
                    fetch(statusPolling.url, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.ok ? r.json() : null)
                        .then(applyStatus)
                        .catch(err => console.warn('Status poll error', err));
                };
                this.time.addEvent({ delay: statusPolling.intervalMs, loop: true, callback: poll });
                poll(); // initial
            }
        },
        update(_time, delta) {
            // Worker movement + animation switching
            if (this.workerState && this.workerState.target) {
                const { sprite, target } = this.workerState;
                const dx = target.x - sprite.x;
                const dy = target.y - sprite.y;
                const dist = Math.hypot(dx, dy);
                if (dist < 2) {
                    sprite.x = target.x;
                    sprite.y = target.y;
                    this.workerState.target = null;
                    // Pull next waypoint if any
                    if (this.workerState.queue.length > 0) {
                        const next = this.workerState.queue.shift();
                        this.workerState.target = next;
                        this._placeOrMoveTargetMarker(next.x, next.y);
                        this._drawWaypointMarkers();
                    } else {
                        this._drawWaypointMarkers();
                    }
                    this._setWorkerAnim('worker-idle');
                } else {
                    const step = moveSpeed * (delta / 1000);
                    const nx = dx / dist;
                    const ny = dy / dist;
                    sprite.x += nx * step;
                    sprite.y += ny * step;
                    // Direction resolution with threshold
                    const ax = Math.abs(nx);
                    const ay = Math.abs(ny);
                    const primary = ay > ax ? 'vertical' : 'horizontal';
                    const dirY = ny < 0 ? 'north' : 'south';
                    const dirX = nx < 0 ? 'west' : 'east';
                    if (primary === 'vertical') {
                        this._setWorkerAnim(`worker-walk-${dirY}`);
                    } else {
                        // Use east/west rows if provided; else idle fallback
                        if (rows.east !== undefined && rows.west !== undefined) {
                            this._setWorkerAnim(`worker-walk-${dirX}`);
                        } else {
                            this._setWorkerAnim('worker-idle');
                        }
                    }
                }
            }
        }
    };

    const config = {
        type: Phaser.AUTO,
        parent: mountId,
        width,
        height,
        backgroundColor,
        scene: sceneDefinition,
        physics: {
            default: 'arcade',
            arcade: { debug }
        }
    };

    return new Phaser.Game(config);
}
