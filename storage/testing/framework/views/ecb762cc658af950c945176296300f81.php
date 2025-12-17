<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Control Panel</title>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

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
            width: min(540px, 100%);
            background: rgba(11, 14, 26, 0.95);
            border-radius: 18px;
            padding: 1.75rem;
            box-shadow: 0 20px 70px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            text-align: center;
        }
        h1 { margin: 0 0 1rem; }
        p { margin: 0 0 1.25rem; color: #cdd7ff; }
        .stack { display: flex; flex-direction: column; gap: 0.75rem; }
        button, .btn {
            display: inline-flex; align-items: center; justify-content: center;
            width: 100%;
            border: none; border-radius: 999px;
            padding: 0.85rem 1.25rem;
            font-weight: 600; font-size: 1rem;
            cursor: pointer; text-decoration: none;
        }
        .btn-primary { background: linear-gradient(120deg, #6366f1, #8b5cf6); color: #fff; box-shadow: 0 12px 25px rgba(99,102,241,0.35); }
        .btn-muted { background: rgba(255,255,255,0.08); color: #cdd7ff; border: 1px solid rgba(255,255,255,0.12); }
        .links { margin-top: 1rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; font-size: 0.9rem; }
        a { color: #9ecbff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.navigation', []);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3461660603-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    <div class="panel">
        <h1>Admin Control Panel</h1>
        <p>Quick links to developer tooling.</p>

        <div class="stack">
            <form action="<?php echo e(route('map.index')); ?>" method="GET">
                <button type="submit" class="btn btn-primary">Map Generator</button>
            </form>

            <form action="<?php echo e(route('profile')); ?>" method="GET">
                <button type="submit" class="btn btn-muted">Change My Password</button>
            </form>
        </div>

        <div class="links">
            <a href="<?php echo e(route('main.entrance')); ?>">Main Menu</a>
        </div>
    </div>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

</body>
</html>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/control-panel.blade.php ENDPATH**/ ?>