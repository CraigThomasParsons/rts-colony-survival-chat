@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto">
  <h2 class="text-xl font-semibold mb-4">Heightmap Preview</h2>
  <livewire:heightmap-preview :map-id="$map->id" />

  <div class="mt-4 flex gap-3">
    <a href="{{ route('map.tilemap.preview', ['mapId' => $map->id]) }}" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded">Tile Map</a>
    <a href="{{ $nextRoute }}" class="inline-block px-4 py-2 bg-blue-600 text-white rounded">Run Step 2</a>
  </div>

  <h3 class="text-lg font-semibold mt-6 mb-2">Heightmap Table</h3>
  <livewire:heightmap-table :map-id="$map->id" />
</div>
@endsection
