@once
    <style>
        .map-preview-grid {
            display: grid;
            gap: 2px;
            padding: 12px;
            border-radius: 0.75rem;
            background: radial-gradient(circle at top, rgba(15, 23, 42, 0.95), rgba(2, 6, 23, 0.85));
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.45);
            grid-auto-rows: 18px;
        }

        .map-preview-tile {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 4px;
            background-size: cover;
            background-position: center;
            transition: opacity 0.2s ease;
        }

        .map-preview-tile:hover {
            opacity: 0.85;
        }

        .tile-water {
            background-image: linear-gradient(145deg, #0ea5e9, #0369a1);
        }

        .tile-sand {
            background-image: linear-gradient(145deg, #fcd34d, #f59e0b);
        }

        .tile-grass {
            background-image: linear-gradient(145deg, #4ade80, #15803d);
        }

        .tile-forest {
            background-image: linear-gradient(145deg, #065f46, #064e3b);
        }

        .tile-hill {
            background-image: linear-gradient(145deg, #9ca3af, #475569);
        }

        .tile-unknown {
            background-image: linear-gradient(145deg, #94a3b8, #64748b);
        }
    </style>
@endonce

<div class="space-y-48">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Surface Map Preview</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Adjust the dimensions/seed and click “Generate” to see a real-time grid preview. Each tile is rendered as a div so we can swap to an actual sprite sheet later.
        </p>
    </div>

    <form wire:submit.prevent="generate" class="grid gap-20 sm:grid-cols-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Width</label>
            <input type="number" min="16" max="96" wire:model="width"
                   placeholder="64"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700" />
            @error('width') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="col-span-full"><br></div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Height</label>
            <input type="number" min="16" max="96" wire:model="height"
                   placeholder="38"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700" />
            @error('height') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="col-span-full"><br></div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Seed (optional)</label>
            <input type="text" wire:model="seed"
                   placeholder="Leave empty for random"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700" />
            @error('seed') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="col-span-full"><br></div>
        <div class="flex flex-wrap items-center gap-10 sm:col-span-4">
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Generate Preview
            </button>
            <div class="col-span-full"><br></div>
            <button type="button" wire:click="generateRandom" title="Roll a fresh seed and preview immediately"
                    class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-800">
                Random Preview
            </button>
        </div>
    </form>

    @if ($grid)
        <div class="space-y-16">
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                <span><span class="font-semibold">Seed:</span> {{ $lastSeed }}</span>
                @foreach ($palette as $key => $meta)
                    <span><span class="font-semibold">{{ $meta['label'] }}:</span> {{ $counts[$key] ?? 0 }}</span>
                @endforeach
            </div>

            <div class="overflow-auto">
                <div class="map-preview-grid"
                     style="grid-template-columns: repeat({{ $width }}, 18px); margin-top: 4rem;">
                    @foreach ($grid as $row)
                        @foreach ($row as $cell)
                            @php($tile = $palette[$cell] ?? ['label' => 'Unknown', 'class' => 'tile-unknown'])
                            <div class="map-preview-tile {{ $tile['class'] }}" title="{{ $tile['label'] }}"></div>
                        @endforeach
                    @endforeach
                </div>
            </div>

            <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                <div class="font-semibold">Legend:</div>
                @foreach ($palette as $meta)
                    <div class="flex items-center gap-1">
                        <span class="inline-block h-4 w-4 rounded {{ $meta['class'] }}"></span>
                        {{ $meta['label'] }}
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Run a preview to visualize the current surface generation parameters.
        </p>
    @endif
</div>
