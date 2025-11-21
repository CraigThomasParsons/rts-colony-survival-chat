@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Load Existing Game</h1>

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded">
                <strong>There were some problems:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="text-sm text-gray-700 mb-6">
            Choose an existing game from the list below, or enter a Map ID to load its generated map.
        </p>

        @if (isset($games) && count($games) > 0)
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Game</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Map ID</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($games as $game)
                            <tr>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $game->name }}</div>
                                    <div class="text-xs text-gray-500">Game #{{ $game->id }}</div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ $game->created_at ? $game->created_at->format('Y-m-d H:i') : '—' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    @if ($game->map)
                                        {{ $game->map->id }}
                                    @else
                                        <span class="text-xs text-gray-400">no map yet</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                    @if ($game->map)
                                        <a href="{{ url('/Map/load/'.$game->map->id.'/') }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                                           Load Map
                                        </a>
                                    @else
                                        <a href="{{ route('game.mapgen.form', ['mapId' => $game->map->id ?? 0]) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                                           Generate Map
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded text-sm text-yellow-800">
                No existing games were found. You can create a new game from the menu.
            </div>
        @endif

        <div class="mb-6">
            <form id="manual-load-form" onsubmit="return redirectToMap();">
                <label for="mapId" class="block text-sm font-medium text-gray-700 mb-1">Load by Map ID</label>
                <div class="flex space-x-3">
                    <input id="mapId" name="mapId" type="number" min="1" placeholder="Enter map id"
                        class="flex-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700">
                        Load Map
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">If you don't know the map id, use the list above or the "Load existing" screen.</p>
            </form>
        </div>

        <div class="flex items-center justify-between">
            <div class="space-x-3">
                <a href="{{ route('game.new') }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Create New Game</a>
                <a href="{{ route('main.entrance') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Back to Menu</a>
            </div>
            <div>
                <a href="{{ route('game.load') }}" class="text-sm text-gray-600 hover:underline">Refresh</a>
            </div>
        </div>
    </div>
</div>

<script>
function redirectToMap() {
    const input = document.getElementById('mapId');
    const id = input.value && input.value.trim();
    if (!id) {
        alert('Please enter a Map ID to load.');
        return false;
    }
    // Basic numeric validation
    if (!/^\d+$/.test(id)) {
        alert('Map ID must be a positive integer.');
        return false;
    }
    // Redirect to existing Map load route
    window.location.href = `/Map/load/${encodeURIComponent(id)}/`;
    return false;
}

// Optional: add simple client-side filtering for the games table (if present)
document.addEventListener('DOMContentLoaded', () => {
    const table = document.querySelector('table');
    if (!table) return;

    // Add a small search input
    const wrapper = table.parentElement;
    const searchContainer = document.createElement('div');
    searchContainer.className = 'mb-3';
    searchContainer.innerHTML = `
        <input id="game-search" placeholder="Search games by name..." class="w-full border-gray-200 rounded px-3 py-2 text-sm" />
    `;
    wrapper.parentElement.insertBefore(searchContainer, wrapper);

    const searchInput = document.getElementById('game-search');
    searchInput.addEventListener('input', () => {
        const q = searchInput.value.trim().toLowerCase();
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const name = row.querySelector('td').innerText.toLowerCase();
            row.style.display = name.includes(q) ? '' : 'none';
        });
    });
});
</script>
@endsection
