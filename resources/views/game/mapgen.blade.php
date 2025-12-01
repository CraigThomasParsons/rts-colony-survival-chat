<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Map Generation — Map #{{ $map->id }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <style>
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Figtree', 'Lato', sans-serif;
            background: radial-gradient(circle at top, #0f172a 0%, #05070b 70%, #020409 100%);
            color: #f4f6ff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .panel {
            width: min(960px, 100%);
            background: rgba(5, 9, 20, 0.92);
            border-radius: 28px;
            padding: 3.25rem;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            gap: 2.25rem;
        }

        h1 {
            margin: 0;
            text-align: center;
            font-size: 2.25rem;
            letter-spacing: 0.04em;
        }

        p.subtitle {
            text-align: center;
            margin: 0;
            color: #c7d6ff;
        }

        .card {
            background: rgba(15, 17, 28, 0.7);
            border-radius: 20px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        label {
            display: block;
            font-size: 0.85rem;
            margin-bottom: 0.35rem;
            color: #cbd5f5;
        }

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(255, 255, 255, 0.08);
            color: inherit;
        }

        input:focus {
            outline: 2px solid rgba(99, 102, 241, 0.65);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1rem;
        }

        .stat {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 0.9rem 1rem;
            text-align: center;
        }

        .stat span {
            display: block;
            font-size: 0.8rem;
            color: #9ea9ca;
        }

        .stat strong {
            display: block;
            font-size: 1.2rem;
            margin-top: 0.15rem;
        }

        button {
            border: none;
            border-radius: 999px;
            padding: 0.9rem 1.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.1s ease, box-shadow 0.15s ease;
        }

        button.primary {
            background: linear-gradient(120deg, #6366f1, #8b5cf6);
            color: #fff;
            box-shadow: 0 15px 35px rgba(99, 102, 241, 0.35);
        }

        button.secondary {
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5f5;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        button:hover {
            transform: translateY(-1px);
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }

        .footer-links a {
            border-radius: 999px;
            padding: 0.55rem 1.3rem;
            text-decoration: none;
            font-weight: 600;
            color: #cbd5f5;
            border: 1px solid rgba(148, 163, 184, 0.3);
        }

        .footer-links a.primary {
            background: linear-gradient(120deg, #9333ea, #2563eb);
            color: #fff;
            border: none;
            box-shadow: 0 12px 25px rgba(99, 102, 241, 0.35);
        }

        ol {
            margin: 0;
            padding-left: 1.25rem;
            color: #c7cfe7;
        }

        ol li {
            margin-bottom: 0.4rem;
        }
    </style>
</head>
<body>
    <div class="panel">
        <div>
            <h1>Map Generation — Map #{{ $map->id }}</h1>
            <p class="subtitle">
                Kick off the automated generation pipeline (seed now auto-randomized). We’ll queue artisan steps and stream logs on the next page.
            </p>
        </div>

        <div class="grid">
            <div class="stat">
                <span>Game</span>
                <strong>{{ $map->name ?? 'Untitled Map' }}</strong>
            </div>
            <div class="stat">
                <span>Width</span>
                <strong>{{ $map->coordinateX ?? '—' }}</strong>
            </div>
            <div class="stat">
                <span>Height</span>
                <strong>{{ $map->coordinateY ?? '—' }}</strong>
            </div>
        </div>

        <div class="card">
            @if (session('status'))
                <div style="margin-bottom:1rem;padding:0.9rem 1rem;border-radius:12px;background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.35);color:#a7f3d0;">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('game.mapgen.start', ['mapId' => $map->id]) }}" style="display:flex; flex-direction:column; gap:1.5rem;">
                @csrf

                {{-- Seed input removed (non-functional) --}}

                <div style="display:flex; flex-wrap:wrap; gap:0.8rem;">
                    <button type="submit" class="primary">Start Map Generation</button>
                    <a href="{{ url('/Map/load/'.$map->id.'/') }}" class="secondary" style="text-decoration:none; display:inline-flex; align-items:center;">
                        View Logs / Status
                    </a>
                </div>
            </form>
        </div>

        <div class="card" style="gap:1rem;">
            <h2 style="margin:0;font-size:1.1rem;">What happens next</h2>
            <ol>
                <li>We queue the artisan steps (<code>map:1init</code> … <code>map:4water</code>) so they run sequentially.</li>
                <li>Logs stream into <code>storage/logs/mapgen-{{ $map->id }}.log</code>.</li>
                <li>If anything fails, you can re-run individual steps from the developer map tools.</li>
            </ol>
        </div>

        <div class="footer-links">
            <a class="primary" href="{{ route('main.entrance') }}">Main Menu</a>
            <a href="{{ route('game.load') }}">Load Game</a>
            <a href="{{ route('control-panel') }}">Control Panel</a>
        </div>
    </div>
</body>
</html>
