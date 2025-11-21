<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Feudal Frontiers')</title>
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
        .container {
            text-align: center;
        }
        h1 {
            font-size: 3rem;
            color: #e2e8f0;
            margin-bottom: 2rem;
        }
        a {
            color: #cbd5e0;
            text-decoration: none;
            font-size: 1.2rem;
            margin-top: 2rem;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        @yield('content')
        <a href="{{ route('main.entrance') }}">Back to Main Menu</a>
    </div>
</body>
</html>