<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Surface Map Preview</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Adjust the dimensions/seed and click “Generate” to see a real-time ASCII preview of the surface layer.
        </p>
    </div>

    <form wire:submit.prevent="generate" class="grid gap-4 sm:grid-cols-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Width</label>
            <input type="number" min="16" max="96" wire:model.defer="width"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700" />
            @error('width') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Height</label>
            <input type="number" min="16" max="96" wire:model.defer="height"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700" />
            @error('height') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="sm:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Seed (optional)</label>
            <input type="text" wire:model.defer="seed"
                   placeholder="Leave empty for random"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700" />
            @error('seed') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center space-x-3 sm:col-span-4">
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Generate Preview
            </button>
            <button type="button" wire:click="generateRandom"
                    class="inline-flex items-center rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-800">
                Random Preview
            </button>
        </div>
    </form>

    @if ($grid)
        <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                <span><span class="font-semibold">Seed:</span> {{ $lastSeed }}</span>
                <span><span class="font-semibold">Water:</span> {{ $counts['water'] ?? 0 }}</span>
                <span><span class="font-semibold">Sand:</span> {{ $counts['sand'] ?? 0 }}</span>
                <span><span class="font-semibold">Grass:</span> {{ $counts['grass'] ?? 0 }}</span>
                <span><span class="font-semibold">Forest:</span> {{ $counts['forest'] ?? 0 }}</span>
                <span><span class="font-semibold">Hills:</span> {{ $counts['hill'] ?? 0 }}</span>
            </div>

            <div class="overflow-auto rounded-md border border-gray-200 bg-black/90 p-3 font-mono text-xs leading-4 text-green-200 dark:border-gray-700">
                @foreach ($grid as $row)
                    <div class="whitespace-nowrap">
                        @foreach ($row as $cell)
                            @php($tile = $palette[$cell] ?? ['symbol' => '?', 'class' => 'text-gray-400'])
                            <span class="{{ $tile['class'] }}">{{ $tile['symbol'] }}</span>
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="flex flex-wrap gap-4 text-xs text-gray-600 dark:text-gray-400">
                <div><span class="font-semibold">Legend:</span></div>
                <div class="flex items-center gap-1"><span class="text-sky-400">~</span> Water</div>
                <div class="flex items-center gap-1"><span class="text-amber-400">.</span> Sand</div>
                <div class="flex items-center gap-1"><span class="text-green-400">,</span> Grass</div>
                <div class="flex items-center gap-1"><span class="text-emerald-500">T</span> Forest</div>
                <div class="flex items-center gap-1"><span class="text-stone-400">^</span> Hill</div>
            </div>
        </div>
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Run a preview to visualize the current surface generation parameters.
        </p>
    @endif
</div>
