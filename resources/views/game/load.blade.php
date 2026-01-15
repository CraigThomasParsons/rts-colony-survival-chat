<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Load Game</title>
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top, #101322 0%, #05070b 60%, #030409 100%);
            color: #f2f5ff;
            font-family: 'Figtree', 'Lato', sans-serif;
            padding: 2rem;
        }
        .panel {
            width: min(720px, 100%);
            background: rgba(11, 14, 26, 0.95);
            border-radius: 18px;
            padding: 1.75rem;
            box-shadow: 0 20px 70px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        h1 { margin: 0 0 1.25rem; text-align: center; }
        .alert { border-radius: 12px; padding: 0.85rem 1rem; margin-bottom: 1rem; font-size: 0.9rem; }
        .alert-success { background: rgba(16,185,129,0.08); border:1px solid rgba(16,185,129,0.35); color:#a7f3d0; }
        .alert-error { background: rgba(248,113,113,0.08); border:1px solid rgba(248,113,113,0.35); color:#fecaca; }
        table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
        th, td { padding: 0.65rem 0.5rem; text-align: left; }
        th { font-size: 0.8rem; letter-spacing: 0.05em; color: #cdd7ff; border-bottom: 1px solid rgba(255,255,255,0.08); }
        td { font-size: 0.9rem; border-bottom: 1px solid rgba(255,255,255,0.05); color: #e5e7f2; }
        .tag { font-size: 0.75rem; color: #b5bdd8; }
        .actions { display: flex; flex-wrap: wrap; gap: 0.6rem; align-items: center; }
        button, .btn {
            display: inline-flex; align-items: center; justify-content: center;
            border: none; border-radius: 999px; padding: 0.65rem 1.25rem;
            font-weight: 600; text-decoration: none; cursor: pointer;
        }
        .btn-primary { background: linear-gradient(120deg, #6366f1, #8b5cf6); color: #fff; box-shadow: 0 12px 25px rgba(99,102,241,0.35); }
        .btn-muted { background: rgba(255,255,255,0.06); border: 1px solid rgba(255,255,255,0.08); color: #cdd7ff; }
        .muted { color: #b5bdd8; font-size: 0.9rem; }
        input {
            width: 100%; padding: 0.65rem 0.85rem; border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.06);
            color: inherit;
        }
        input:focus { outline: 2px solid rgba(147, 197, 253, 0.55); }
        .footer-links { margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.9rem; }
        a { color: #9ecbff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Load Existing Game</h1>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <strong>There were some problems:</strong>
                <ul style="margin: 0.5rem 0 0 1.25rem;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="muted">Choose an existing game below or jump directly to a map ID.</p>

        @if (isset($games) && count($games) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Game</th>
                        <th>Created</th>
                        <th>Maps</th>
                        <th style="text-align:right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($games as $game)
                        <tr>
                            <td>
                                <div>{{ $game->name }}</div>
                                <div class="tag">Game #{{ $game->id }}</div>
                            </td>
                            <td class="muted">{{ $game->created_at ? $game->created_at->format('Y-m-d H:i') : '—' }}</td>
                            <td class="muted">
                                @if ($game->maps && $game->maps->count())
                                    @foreach ($game->maps as $map)
                                        <span class="tag">Map #{{ $map->id }}</span>@if(!$loop->last), @endif
                                    @endforeach
                                @else
                                    <span class="tag">no maps yet</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div class="actions" style="justify-content: flex-end;">
                                    @if ($game->maps && $game->maps->count())
                                        @foreach ($game->maps as $map)
                                            <a class="btn btn-primary" href="{{ url('/Map/load/'.$map->id.'/') }}">Load #{{ $map->id }}</a>
                                        @endforeach
                                        <a class="btn btn-muted" href="{{ route('game.maps.table', ['game' => $game->id]) }}">Open Maps Table</a>
                                    @else
                                        <a class="btn btn-muted" href="{{ route('game.mapgen.form', ['mapId' => 0]) }}">Generate Map</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="alert alert-error" style="border-color: rgba(251, 191, 36, 0.35); background: rgba(251, 191, 36, 0.08); color: #fcd34d;">
                No existing games found. You can create a new game from the menu.
            </div>
        @endif

        <div style="margin: 1.25rem 0;">
            <form onsubmit="return redirectToMap();" style="display:flex; gap:0.75rem; align-items:center;">
                <input id="mapId" name="mapId" type="number" min="1" placeholder="Enter map id" />
                <button type="submit" class="btn btn-primary">Load Map</button>
            </form>
            <p class="tag" style="margin-top:0.5rem;">If you don't know the map id, pick from the list above.</p>
        </div>

        <div class="footer-links">
            <a class="btn btn-primary" href="{{ route('game.new') }}" style="padding:0.6rem 1.1rem;">Create New Game</a>
            <a class="btn btn-muted" href="{{ route('main.entrance') }}" style="padding:0.6rem 1.1rem;">Main Menu</a>
            <a class="btn btn-muted" href="{{ route('control-panel') }}" style="padding:0.6rem 1.1rem;">Control Panel</a>
        </div>
    </div>

    <script>
        function redirectToMap() {
            const input = document.getElementById('mapId');
            const id = input.value && input.value.trim();
            if (!id) { alert('Please enter a Map ID.'); return false; }
            if (!/^\d+$/.test(id)) { alert('Map ID must be a positive integer.'); return false; }
            window.location.href = `/Map/load/${encodeURIComponent(id)}/`;
            return false;
        }
    </script>
</body>
</html>
