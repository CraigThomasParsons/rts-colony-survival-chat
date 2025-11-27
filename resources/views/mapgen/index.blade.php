<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Map Generator Preview</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
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
            width: min(1100px, 100%);
            background: rgba(5, 9, 20, 0.92);
            border-radius: 28px;
            padding: 4.5rem 4.5rem 3rem;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            gap: 3rem;
        }

        h1 {
            margin: 0;
            font-size: 2.2rem;
            font-weight: 600;
            letter-spacing: 0.05em;
            text-align: center;
        }

        p.subtitle {
            margin: 1.25rem auto 0;
            text-align: center;
            color: #c8d3ff;
            max-width: 640px;
        }

        .footer-links {
            margin-top: 2.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }

        .footer-links a {
            border-radius: 999px;
            padding: 0.6rem 1.35rem;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.1s ease, box-shadow 0.15s ease;
        }

        .footer-links a.primary {
            background: linear-gradient(120deg, #6366f1, #8b5cf6);
            color: white;
            box-shadow: 0 15px 30px rgba(99, 102, 241, 0.35);
        }

        .footer-links a.muted {
            background: rgba(148, 163, 184, 0.15);
            color: #cbd5f5;
            border: 1px solid rgba(148, 163, 184, 0.4);
        }

        .footer-links a:hover {
            transform: translateY(-1px);
        }

        .livewire-wrapper {
            background: rgba(15, 17, 28, 0.65);
            border-radius: 24px;
            padding: 3.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            gap: 3rem;
        }

        .livewire-wrapper input,
        .livewire-wrapper button,
        .livewire-wrapper select {
            font-family: 'Figtree', sans-serif;
        }

        .livewire-wrapper button {
            border-radius: 999px !important;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Map Generator Preview</h1>
        <p class="subtitle">
            Tune your seed and dimensions, watch the surface render tile-by-tile, and get ready to swap in a sprite sheet later.
        </p>

        <div class="livewire-wrapper">
            <livewire:map-generator-preview />
        </div>

        <div class="footer-links">
            <a class="primary" href="{{ route('main.entrance') }}">Main Menu</a>
            <a class="muted" href="{{ route('game.new') }}">Create New Game</a>
            <a class="muted" href="{{ route('control-panel') }}">Control Panel</a>
        </div>
    </div>

    @livewireScripts
</body>
</html>
