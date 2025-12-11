<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Create New Game</title>
    <style>
        :root {
            color-scheme: dark;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Figtree', 'Lato', sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #101322 0%, #05070b 60%, #030409 100%);
            color: #f2f5ff;
            padding: 2rem;
        }

        .panel {
            width: min(640px, 100%);
            background: rgba(11, 14, 26, 0.95);
            border-radius: 18px;
            padding: 2rem;
            box-shadow: 0 20px 70px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        h1 {
            margin: 0 0 1.5rem 0;
            font-size: 2rem;
            text-align: center;
            letter-spacing: 0.05em;
        }

        label {
            display: block;
            font-size: 0.9rem;
            margin-bottom: 0.35rem;
            color: #cdd7ff;
        }

        input {
            width: 100%;
            padding: 0.75rem 0.85rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.06);
            color: inherit;
        }

        input:focus {
            outline: 2px solid rgba(147, 197, 253, 0.55);
        }

        .grid {
            display: grid;
            gap: 1rem;
        }

        @media (min-width: 520px) {
            .grid.two {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        .actions {
            margin-top: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        button {
            border: none;
            border-radius: 999px;
            padding: 0.85rem 1.75rem;
            font-weight: 600;
            font-size: 0.95rem;
            background: linear-gradient(120deg, #6366f1, #8b5cf6);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 12px 25px rgba(99, 102, 241, 0.35);
        }

        button:hover {
            filter: brightness(1.08);
        }

        a {
            color: #9ecbff;
            text-decoration: none;
            font-size: 0.9rem;
        }

        a:hover {
            text-decoration: underline;
        }

        .alert {
            border-radius: 12px;
            padding: 0.85rem 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #a7f3d0;
        }

        .alert-error {
            background: rgba(248, 113, 113, 0.08);
            border: 1px solid rgba(248, 113, 113, 0.35);
            color: #fecaca;
        }

        hr {
            border: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin: 2rem 0;
        }

        .notes {
            font-size: 0.9rem;
            color: #c3c9df;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Create New Game</h1>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>There were some problems with your input:</strong>
                <ul style="margin: 0.5rem 0 0 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('game.create') }}" class="grid gap">
            @csrf

            <div>
                <label for="name">Game Name</label>
                <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name') }}" />
            </div>

            <div class="grid two">
                <div>
                    <label for="width">Map Width</label>
                    <input id="width" name="width" type="number" required min="32" max="128" value="{{ old('width', config('app.map_default_width', 64)) }}" />
                </div>
                <div>
                    <label for="height">Map Height</label>
                    <input id="height" name="height" type="number" required min="32" max="128" value="{{ old('height', config('app.map_default_height', 38)) }}" />
                </div>
            </div>

            {{-- Removed seed input (unused / non-functional) --}}

            <div class="actions">
                <button type="submit">Create Game</button>
                <a href="{{ route('game.load') }}">Load existing game</a>
                <a href="{{ route('control-panel') }}">Control Panel</a>
                <a href="{{ route('main.entrance') }}">Main Menu</a>
            </div>
        </form>

        <hr />

        <div class="notes">
            <strong>After creating a game:</strong>
            <p>
                You'll be redirected to the map generator to watch each automated generation step. Prefer to tinker manually?
                The developer Map tools are still available from the control panel.
            </p>
            <p style="font-size: 0.8rem; color: #aeb5ce;">
                Width/height guide: 32–128. (Seed removed from UI — generation now auto-randomizes internally.)
            </p>
        </div>
    </div>
</body>
</html>
