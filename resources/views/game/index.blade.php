@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-8 space-y-8">
    <h1 class="text-2xl font-semibold">Games</h1>

    <div class="bg-gray-800/60 p-4 rounded">
        <h2 class="text-lg font-medium mb-3">Create New Game</h2>
        <form method="POST" action="{{ route('game.create') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm mb-1" for="name">Name</label>
                <input name="name" id="name" required class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm" placeholder="My Colony" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1" for="width">Width</label>
                    <input name="width" id="width" type="number" min="32" max="128" value="64" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm mb-1" for="height">Height</label>
                    <input name="height" id="height" type="number" min="32" max="128" value="64" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm" />
                </div>
            </div>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded text-sm font-medium">Create & Generate</button>
        </form>
    </div>

    <div>
        <h2 class="text-lg font-medium mb-3">Recent Games</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-700">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Maps</th>
                        <th class="px-3 py-2 text-left">Created</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($games as $game)
                    <tr class="border-t border-gray-700">
                        <td class="px-3 py-2">{{ $game->name }}</td>
                        <td class="px-3 py-2">
                            {{ $game->maps->count() }}
                            @if($game->maps->first())
                                <div class="text-xs text-gray-400">status: {{ $game->maps->first()->status ?? '—' }}</div>
                            @endif
                        </td>
                        <td class="px-3 py-2">{{ $game->created_at?->diffForHumans() }}</td>
                        <td class="px-3 py-2 space-x-2">
                            @if($game->maps->first())
                                @php($m = $game->maps->first())
                                <a href="{{ route('map.editor', ['mapId' => $m->id]) }}" class="text-blue-400 hover:text-blue-300 underline">Editor</a>
                                <a href="{{ route('game.mapgen.form', ['mapId' => $game->maps->first()->id]) }}" class="text-indigo-400 hover:text-indigo-300 underline">Generate</a>
                                <a href="{{ route('mapgen.preview', ['mapId' => $game->maps->first()->id]) }}" class="text-green-400 hover:text-green-300 underline">Preview</a>
                                @if(($m->status ?? null) === 'ready')
                                    <form method="POST" action="{{ route('game.start', ['game' => $game->id]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-emerald-400 hover:text-emerald-300 underline">Start Game</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-gray-500">No games yet. Create one above.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
