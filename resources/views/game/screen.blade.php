@extends('layouts.app')
@section('content')
<div class="flex flex-col h-full">
    <div class="flex-1 overflow-hidden bg-slate-900 relative">
         @livewire('game-map', ['game' => $game, 'map' => $map])
    </div>
</div>
@endsection
