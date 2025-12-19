<div id="game-map-container" class="overflow-auto w-full h-full bg-gray-900 border border-gray-700 shadow-lg cursor-grab active:cursor-grabbing" style="height: 100%; width: 100%; overflow: auto;">
    <div id="select-indicator"></div>
    <div style="display: flex; flex-direction: column; width: max-content; line-height: 0;" class="relative bg-black">
        @for($y = $map->coordinateY - 1; $y >= 0; $y--)
            <div style="display: flex; flex-wrap: nowrap;">
                @for($x = 0; $x < $map->coordinateX; $x++)
                    @php 
                        $tile = $grid[$y][$x] ?? null;
                        $tooltip = $tile ? "Coordinates: {$tile->coordinateX},{$tile->coordinateY}<br>Type: " . ($tile->tileType->name ?? 'Unknown') : null;
                    @endphp

                    <div class="relative w-16 h-16 group flex-shrink-0" id="tile-{{$x}}-{{$y}}" data-x="{{$x}}" data-y="{{$y}}" @if($tile) data-tippy-content="{{ $tooltip }}" @endif>
                        @if($tile)
                            {{-- Base Layer --}}
                            <img 
                                src="{{ asset($this->getTileAsset($tile)) }}"
                                class="w-full h-full block rendering-pixelated pointer-events-none select-none"
                                draggable="false"
                                loading="lazy"
                                onerror="this.src='https://placehold.co/64x64/228822/FFFFFF?text=?'"
                            >
                            
                            {{-- Overlay Layer (Trees) --}}
                            @if($overlay = $this->hasOverlay($tile))
                                <img 
                                    src="{{ asset($overlay) }}"
                                    class="absolute top-0 left-0 w-full h-full block pointer-events-none rendering-pixelated select-none"
                                    draggable="false"
                                    loading="lazy"
                                >
                            @endif
                        @else
                            {{-- Empty/Missing Tile Placeholder --}}
                            <div class="w-full h-full bg-black"></div>
                        @endif
                    </div>
                @endfor
            </div>
        @endfor

        {{-- Buildings Layer --}}
        <div class="absolute inset-0 pointer-events-none">
             @foreach($buildings as $building)
                @php
                    $left = $building->x * 64;
                    $bottom = $building->y * 64;
                @endphp
                <div 
                    class="selectable-building absolute w-16 h-16 pointer-events-auto"
                    style="left: {{$left}}px; bottom: {{$bottom}}px; width: 64px; height: 64px;"
                    data-id="{{ $building->id }}"
                    data-tippy-content="{{ $building->buildingType->name }} ({{$building->hitpoints}} HP)"
                >
                    <img 
                        src="{{ asset($this->getBuildingAsset($building)) }}" 
                        class="w-full h-full block rendering-pixelated pointer-events-none select-none"
                        draggable="false"
                    >
                    {{-- Selection Highlight --}}
                    <div class="selection-overlay absolute inset-0 bg-green-500 opacity-0 pointer-events-none border-2 border-transparent"></div>
                </div>
             @endforeach
        </div>

    </div>
    
    <style>
        .rendering-pixelated {
            image-rendering: pixelated;
        }
        .cursor-grab { cursor: grab; }
        .cursor-grabbing, .cursor-grab:active { cursor: grabbing; }

        #select-indicator {
            background-color: rgba(15, 255, 15, 0.3);
            border: 1px solid rgb(15, 182, 15);
            position: absolute;
            display: none;
            z-index: 50;
            pointer-events: none; 
        }

        /* Selected State */
        .selectable-building.selected .selection-overlay {
            opacity: 0.4;
            border-color: rgb(50, 255, 50);
        }
    </style>
</div>

<!-- Tippy.js Dependencies -->
<script src="https://unpkg.com/@popperjs/core@2"></script>
<script src="https://unpkg.com/tippy.js@6"></script>
<script>
    document.addEventListener('livewire:navigated', () => {
        initTippy();
        initMapInteraction();
        initKeyboardNavigation();
    });
    document.addEventListener('DOMContentLoaded', () => {
        initTippy();
        initMapInteraction();
        initKeyboardNavigation();
    });

    function initTippy() {
        tippy('[data-tippy-content]', {
            allowHTML: true,
            theme: 'light',
        });
    }

    function initMapInteraction() {
        const container = document.getElementById('game-map-container');
        const selectionBox = document.getElementById('select-indicator');
        if(!container || !selectionBox) return;

        let state = {
            isPanning: false,
            isSelecting: false,
            pan: { startX: 0, startY: 0, scrollLeft: 0, scrollTop: 0 },
            select: { startX: 0, startY: 0 }
        };

        container.addEventListener('contextmenu', (e) => e.preventDefault());

        container.addEventListener('mousedown', (e) => {
            if (e.button === 2) {
                state.isPanning = true;
                container.classList.add('cursor-grabbing');
                container.classList.remove('cursor-grab');
                
                state.pan.startX = e.pageX - container.offsetLeft;
                state.pan.startY = e.pageY - container.offsetTop;
                state.pan.scrollLeft = container.scrollLeft;
                state.pan.scrollTop = container.scrollTop;
            }
            else if (e.button === 0) {
                state.isSelecting = true;
                
                const rect = container.getBoundingClientRect();
                const scrollLeft = container.scrollLeft;
                const scrollTop = container.scrollTop;
                
                const x = e.clientX - rect.left + scrollLeft;
                const y = e.clientY - rect.top + scrollTop;

                state.select.startX = x;
                state.select.startY = y;

                selectionBox.style.left = x + 'px';
                selectionBox.style.top = y + 'px';
                selectionBox.style.width = '0px';
                selectionBox.style.height = '0px';
                selectionBox.style.display = 'block';

                if (!e.shiftKey) {
                    document.querySelectorAll('.selectable-building.selected').forEach(el => el.classList.remove('selected'));
                }
            }
        });

        window.addEventListener('mouseup', (e) => { 
            if (state.isPanning) {
                state.isPanning = false;
                container.classList.remove('cursor-grabbing');
                container.classList.add('cursor-grab');
            }
            if (state.isSelecting) {
                state.isSelecting = false;
                selectionBox.style.display = 'none';
                checkSelectionCollision();
            }
        });

        container.addEventListener('mousemove', (e) => {
            if (state.isPanning) {
                e.preventDefault();
                const x = e.pageX - container.offsetLeft;
                const y = e.pageY - container.offsetTop;
                const walkX = (x - state.pan.startX);
                const walkY = (y - state.pan.startY);
                container.scrollLeft = state.pan.scrollLeft - walkX;
                container.scrollTop = state.pan.scrollTop - walkY;
            }
            else if (state.isSelecting) {
                e.preventDefault(); 
                
                const rect = container.getBoundingClientRect();
                const scrollLeft = container.scrollLeft;
                const scrollTop = container.scrollTop;

                const currentX = e.clientX - rect.left + scrollLeft;
                const currentY = e.clientY - rect.top + scrollTop;

                const startX = state.select.startX;
                const startY = state.select.startY;

                const width = Math.abs(currentX - startX);
                const height = Math.abs(currentY - startY);
                const newLeft = (currentX < startX) ? currentX : startX;
                const newTop = (currentY < startY) ? currentY : startY;

                selectionBox.style.width = width + 'px';
                selectionBox.style.height = height + 'px';
                selectionBox.style.left = newLeft + 'px';
                selectionBox.style.top = newTop + 'px';

                 checkSelectionCollision();
            }
        });

        function checkSelectionCollision() {
            const boxLeft = parseInt(selectionBox.style.left || 0);
            const boxTop = parseInt(selectionBox.style.top || 0);
            const boxWidth = parseInt(selectionBox.style.width || 0);
            const boxHeight = parseInt(selectionBox.style.height || 0);
            const boxRight = boxLeft + boxWidth;
            const boxBottom = boxTop + boxHeight;

            const targets = document.querySelectorAll('.selectable-building');
            
            targets.forEach(target => {
                const targetRect = target.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                
                const targetLeft = targetRect.left - containerRect.left + container.scrollLeft;
                const targetTop = targetRect.top - containerRect.top + container.scrollTop;
                const targetRight = targetLeft + targetRect.width;
                const targetBottom = targetTop + targetRect.height;

                const isOverlapping = (
                    boxLeft < targetRight &&
                    boxRight > targetLeft &&
                    boxTop < targetBottom &&
                    boxBottom > targetTop
                );

                if (isOverlapping) {
                    target.classList.add('selected');
                } else {
                    target.classList.remove('selected');
                }
            });
        }
    }

    function initKeyboardNavigation() {
        const container = document.getElementById('game-map-container');
        if (!container) return;

        const keys = {
            w: false,
            a: false,
            s: false,
            d: false,
            ArrowUp: false,
            ArrowLeft: false,
            ArrowDown: false,
            ArrowRight: false
        };

        const speed = 15; // Scroll speed px/tick

        window.addEventListener('keydown', (e) => {
            // Debug Log
            // console.log('Key down:', e.key); 
            
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
            if (keys.hasOwnProperty(e.key) || keys.hasOwnProperty(e.key.toLowerCase())) {
                const k = e.key.length === 1 ? e.key.toLowerCase() : e.key;
                keys[k] = true;
                // prevent default scroll for arrows
                if(['ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'].includes(e.key)) {
                    e.preventDefault();
                }
            }
        });

        window.addEventListener('keyup', (e) => {
            if (keys.hasOwnProperty(e.key) || keys.hasOwnProperty(e.key.toLowerCase())) {
                const k = e.key.length === 1 ? e.key.toLowerCase() : e.key;
                keys[k] = false;
            }
        });

        function loop() {
            // Re-fetch container to handle Livewire DOM updates
            const currentContainer = document.getElementById('game-map-container');
            if (!currentContainer) return;

            let dx = 0;
            let dy = 0;

            if (keys.w || keys.ArrowUp) dy -= speed;
            if (keys.s || keys.ArrowDown) dy += speed;
            if (keys.a || keys.ArrowLeft) dx -= speed;
            if (keys.d || keys.ArrowRight) dx += speed;

            if (dx !== 0 || dy !== 0) {
                currentContainer.scrollLeft += dx;
                currentContainer.scrollTop += dy;
            }

            requestAnimationFrame(loop);
        }

        requestAnimationFrame(loop);
    }
</script>
