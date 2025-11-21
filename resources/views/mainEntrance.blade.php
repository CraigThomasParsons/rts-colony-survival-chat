<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        @import url(//fonts.googleapis.com/css?family=Lato:700);

        body {
            margin:0;
            font-family:'Lato', sans-serif;
            text-align:center;
            color: #999;
        }

        .welcome {
            width: 300px;
            height: 200px;
            position: absolute;
            left: 50%;
            top: 50%;
            margin-left: -150px;
            margin-top: -100px;
        }

        /* Main menu: vertical pill-style menu */
        nav.main-menu {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }

        nav.main-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 10px;
            align-items: center;
            min-width: 200px;
        }

        /* Each list item uses full-width pill link */
        nav.main-menu ul li {
            display: block;
            width: 100%;
        }

        /* Pill link style */
        nav.main-menu ul li a {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 10px 18px;
            border-radius: 9999px;
            background: linear-gradient(180deg, #111827 0%, #0f172a 100%);
            color: #ffffff;
            font-weight: 700;
            text-decoration: none;
            text-align: center;
            box-shadow: 0 4px 8px rgba(2,6,23,0.35);
            transition: transform .08s ease, box-shadow .12s ease, background .12s ease;
        }

        nav.main-menu ul li a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(2,6,23,0.45);
            background: linear-gradient(180deg, #0b1220 0%, #0d1726 100%);
        }

        nav.main-menu ul li a:active {
            transform: translateY(0);
            box-shadow: 0 3px 6px rgba(2,6,23,0.35);
        }

        /* Secondary (less prominent) links can use a lighter pill - add class="secondary" to the anchor */
        nav.main-menu ul li a.secondary {
            background: linear-gradient(180deg, #4b5563 0%, #374151 100%);
            color: #ffffff;
            font-weight: 600;
        }

        a, a:visited {
            text-decoration:none;
            color: inherit;
        }

        h1 {
            font-size: 32px;
            margin: 16px 0 0 0;
        }
    </style>

    <title>Game: Experimental Task Manager</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet"/>

    <!-- Compiled and minified CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css"/>
    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.classless.min.css"/> -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@exampledev/new.css@1.1.2/new.min.css"/>

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>


    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

                            <li><a href="{{ route('logout') }}">Logout</a></li>
                        @endif
                    @endauth

                    <li><a href="{{ route('settings') }}">Settings</a></li>
                </ul>
            </nav>
        </div>
    </body>
</html>
