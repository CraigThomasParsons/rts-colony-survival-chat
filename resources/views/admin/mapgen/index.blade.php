@extends('layouts.app')

@section('content')
<div class="container p-6">
    <h1 class="text-2xl font-bold mb-4">Map Generation Admin</h1>

    <div class="space-y-4">
        <h2 class="font-semibold">Generator Preview</h2>
        <livewire:mapgen-preview />
    </div>

    <div class="mt-6">
        <h2 class="font-semibold">Editor</h2>
        <a href="{{ route('admin.mapgen.editor') }}" class="text-blue-600">Open Map Editor</a>
    </div>
</div>
@endsection
