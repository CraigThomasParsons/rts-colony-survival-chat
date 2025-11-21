@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Map Generation — Map #{{ $map->id }}</h1>

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-6 text-sm text-gray-700">
            <p>
                You're creating the map for <strong>{{ $map->name ?? 'Unnamed Game' }}</strong>.
                Map dimensions: <strong>{{ $map->width ?? '—' }} × {{ $map->height ?? '—' }}</strong>.
            </p>
            <p class="mt-2">
                Enter an integer seed to produce repeatable maps, or leave the seed blank to let the generator pick one for you.
                After you submit, map generation steps will run in the background and the page will redirect to the map load page where you can
                observe progress and view logs.
            </p>
        </div>

        <form method="POST" action="{{ route('game.mapgen.start', ['mapId' => $map->id]) }}" class="space-y-4">
            @csrf

            <div>
                <label for="seed" class="block text-sm font-medium text-gray-700">Map Seed (optional)</label>
                <input id="seed" name="seed" type="text" inputmode="numeric"
                       value="{{ old('seed', $map->seed ?? '') }}"
                       placeholder="e.g. 12345678"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                <p class="text-xs text-gray-500 mt-1">
                    Tip: numeric seeds produce deterministic results. You can try values like <code>1</code>, <code>42</code> or a large random integer.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Width</label>
                    <div class="mt-1 text-sm text-gray-800">{{ $map->width ?? '—' }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Height</label>
                    <div class="mt-1 text-sm text-gray-800">{{ $map->height ?? '—' }}</div>
                </div>
            </div>

            <div class="flex items-center space-x-3 mt-4">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Start Map Generation
                </button>

                <a href="{{ url('/Map/load/'.$map->id.'/') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">
                    Check Map Status
                </a>

                <a href="{{ route('main.entrance') }}" class="text-sm text-gray-600 hover:underline">Back to Menu</a>
            </div>
        </form>

        <hr class="my-6">

        <div class="text-sm text-gray-700">
            <h2 class="font-semibold mb-2">What happens next</h2>
            <ol class="list-decimal list-inside space-y-2">
                <li>The webserver will start background artisan commands to run the map steps:
                    <code>map:1init</code>, <code>map:2firststep-tiles</code>, <code>map:3mountain</code>, <code>map:4water</code>.
                </li>
                <li>Output is written to <code>storage/logs/mapgen-{{ $map->id }}.log</code>. You can check progress on the map load page.</li>
                <li>If something goes wrong, the log will include errors and you may re-run steps manually from the developer tools.</li>
            </ol>
        </div>

        <div class="mt-6 text-xs text-gray-500">
            <p><strong>Notes:</strong> background commands are launched detached from the web request. If your environment needs a specific PHP binary or
                custom path to artisan update the server configuration. Map generation can be CPU intensive for large sizes.</p>
        </div>
    </div>
</div>
@endsection
