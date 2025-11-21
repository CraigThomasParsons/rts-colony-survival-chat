@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-4">Create New Game</h1>

        @if (session('status'))
            <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 rounded">
                <strong>There were some problems with your input:</strong>
                <ul class="mt-2 list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li class="text-sm">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('game.create') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Game Name</label>
                <input id="name" name="name" type="text" required maxlength="255"
                    value="{{ old('name') }}"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="width" class="block text-sm font-medium text-gray-700">Map Width</label>
                    <input id="width" name="width" type="number" required min="32" max="128" value="{{ old('width', 64) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                </div>

                <div>
                    <label for="height" class="block text-sm font-medium text-gray-700">Map Height</label>
                    <input id="height" name="height" type="number" required min="32" max="128" value="{{ old('height', 38) }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
            </div>

            <div class="flex items-center space-x-3">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    Create Game
                </button>

                <a href="{{ route('game.load') }}" class="text-sm text-indigo-600 hover:underline">Load existing game</a>

                <a href="{{ route('main.entrance') }}" class="text-sm text-gray-600 hover:underline">Main Menu</a>
            </div>
        </form>

        <hr class="my-6">

        <div class="text-sm text-gray-700">
            <h2 class="font-semibold mb-2">After creating a game</h2>
            <p class="mb-2">
                After you create a game you'll be redirected to a map-generation page where you can enter a seed and start the automated
                map creation steps (these run in the background). If you prefer to generate maps manually you can also use the Map tools under the
                developer menu.
            </p>

            <p class="text-xs text-gray-500">
                Recommended seeds: a random integer, or leave blank to use an automatic seed. Width/height influence the generated map size:
                keep values between 32 and 128.
            </p>
        </div>
    </div>
</div>
@endsection
