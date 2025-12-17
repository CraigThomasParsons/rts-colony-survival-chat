<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        @import url(//fonts.googleapis.com/css?family=Lato:700);

        body {
            margin: 0;
            font-family: 'Lato', sans-serif;
            text-align: center;
            color: #cdd7ff;
            background: linear-gradient(180deg, rgba(6,11,25,0.95), rgba(3,3,12,0.9)), url('<?php echo e(asset('images/login-bg.png')); ?>') center/cover fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        body.light-mode {
            background: url('<?php echo e(asset('images/login-bg.png')); ?>') center/cover fixed;
            color: #1f2937;
        }

        .title-container {
            width: 100%;
            max-width: 480px;
            margin: auto;
            padding: 24px;
            background: rgba(14, 17, 35, 0.92);
            border-radius: 24px;
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255,255,255,0.06);
            transition: background-color 0.2s ease;
        }

        body.light-mode .title-container {
            background: rgba(255, 255, 255, 0.9);
            box-shadow: 0 15px 45px rgba(15, 23, 42, 0.15);
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
        .main-menu {
            margin-top: 16px;
            display: flex;
            justify-content: center;
        }

        .main-menu ul {
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
        .main-menu ul li {
            display: block;
            width: 100%;
        }

        /* Pill link style */
        .main-menu ul li a {
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

        .main-menu ul li a:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(2,6,23,0.45);
            background: linear-gradient(180deg, #0b1220 0%, #0d1726 100%);
        }

        .main-menu ul li a:active {
            transform: translateY(0);
            box-shadow: 0 3px 6px rgba(2,6,23,0.35);
        }

        /* Secondary (less prominent) links can use a lighter pill - add class="secondary" to the anchor */
        .main-menu ul li a.secondary {
            background: linear-gradient(180deg, #4b5563 0%, #374151 100%);
            color: #ffffff;
            font-weight: 600;
        }

        body.light-mode .main-menu ul li a {
            background: linear-gradient(180deg, #f0f4ff 0%, #dbe4ff 100%);
            color: #0f172a;
            box-shadow: 0 4px 8px rgba(15, 23, 42, 0.2);
        }

        body.light-mode .main-menu ul li a:hover {
            box-shadow: 0 8px 16px rgba(15, 23, 42, 0.2);
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

    <!-- Compiled and minified JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>


    <!-- Styles -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
    <body data-mode="dark">
        <div class="title-container">
            <h1>Feudal Frontiers</h1>
            <div class="main-menu">
                <ul>
                    <li><a href="<?php echo e(route('game.new')); ?>">New Game</a></li>

                    <li><a href="<?php echo e(route('game.load')); ?>">Load Game</a></li>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(Auth::user()->isAdmin()): ?>
                            <li><a href="<?php echo e(route('control-panel')); ?>">Control Panel</a></li>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                    <li><a href="<?php echo e(route('settings')); ?>">Settings</a></li>
                </ul>
            </div>
        </div>

        <script>
            const body = document.body;
            function setMode(mode) {
                if (mode === 'light') {
                    body.classList.add('light-mode');
                    body.dataset.mode = 'light';
                } else {
                    body.classList.remove('light-mode');
                    body.dataset.mode = 'dark';
                }
                localStorage.setItem('ff-theme', mode);
            }
            const stored = localStorage.getItem('ff-theme');
            setMode(stored === 'light' ? 'light' : 'dark');
        </script>
    </body>
</html>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mainEntrance.blade.php ENDPATH**/ ?>