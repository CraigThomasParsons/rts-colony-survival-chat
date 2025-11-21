@extends('layouts.game')

@section('title', 'Game View: ' . $game->name)

@section('content')
    <h1>{{ $game->name }}</h1>

    @if ($game->map)
        <p>Map Loaded: {{ $game->map->width }} x {{ $game->map->height }}</p>
        <p>(Game interface will go here)</p>
    @else
        <p>No map has been generated for this game yet.</p>
    @endif
@endsection
