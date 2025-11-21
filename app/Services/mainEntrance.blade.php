<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Feudal Frontiers</title>
    <style>
        body {
            background-color: #1a202c;
            color: #a0aec0;
            font-family: 'Nunito', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .title-container {
            text-align: center;
        }
        h1 {
            font-size: 4rem;
            color: #e2e8f0;
            margin-bottom: 2rem;
        }
        .main-menu ul {
            list-style: none;
            padding: 0;
        }
        .main-menu li {
            margin: 1rem 0;
        }
        .main-menu a {
            color: #cbd5e0;
            text-decoration: none;
            font-size: 1.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid #4a5568;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .main-menu a:hover {
            background-color: #4a5568;
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="title-container">
        <h1>Feudal Frontiers</h1>
        <nav class="main-menu">
            <ul>
                <li><a href="{{ route('game.new') }}">New Game</a></li>
                <li><a href="{{ route('game.load') }}">Load Game</a></li>
                @auth
                    {{-- Assuming an 'isAdmin' method or property on the User model --}}
                    @if(Auth::user()->isAdmin())
                        <li><a href="{{ route('control-panel') }}">Control Panel</a></li>
                    @endif
                @endauth
                <li><a href="{{ route('settings') }}">Settings</a></li>
            </ul>
        </nav>
    </div>
</body>
</html>