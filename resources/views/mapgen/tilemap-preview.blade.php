@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <a href="{{ route('map.editor', ['mapId' => $mapId]) }}" class="text-blue-400 hover:underline">Back to Map Editor</a>
    <h1 class="text-3xl font-semibold text-gray-100 mb-4">Tile Map Preview</h1>
    <div id="tileCounts" class="text-sm text-gray-300 mb-3">Counts: <span class="text-gray-400">loading…</span></div>
    @livewire('terrain-map', ['mapId' => $mapId])
</div>
@endsection
