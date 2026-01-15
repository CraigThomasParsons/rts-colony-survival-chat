<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Laravel')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

        <!-- Materialize + App Scripts -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" integrity="sha512-1m0RsmYf7v1jFZp09wDJF60p0ISjGH8GMWz3KBGM7rCNRLtLwBEmp6kAMPx/+4vOB0fOkH1hpV2Q0Qp8+4d0Bw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
        
        <?php
            $hasManifest = file_exists(public_path('build/manifest.json'));
        ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasManifest): ?>
            <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
        <?php else: ?>
            <!-- Vite manifest missing: using prebuilt assets fallback -->
            <link rel="stylesheet" href="/build/assets/app-C24ONnXZ.css" />
            <script defer src="/build/assets/app-CUzlGF_f.js"></script>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <style>
            body.theme-dark {
                background: linear-gradient(180deg, rgba(6,11,25,0.95), rgba(3,3,12,0.9)), url('<?php echo e(asset('images/login-bg.png')); ?>') center/cover fixed;
                color: #f4f6ff;
            }
            body.theme-light {
                background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(219,233,255,0.85)), url('<?php echo e(asset('images/login-bg.png')); ?>') center/cover fixed;
                color: #1f2937;
            }
            /* Navbar styling */
            .topnav {
                backdrop-filter: blur(6px);
                background: linear-gradient(0deg, rgba(12,14,22,0.92) 0%, rgba(10,12,18,0.92) 100%);
                border-bottom: 1px solid rgba(255,215,0,0.22);
                box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            }
            .topnav .title {
                color: #f4f1e6;
                text-shadow: 0 1px 0 rgba(0,0,0,0.6);
            }
            .topnav .link {
                color: #d6d9ff;
                transition: color 0.2s ease;
            }
            .topnav .link:hover {
                color: #ffffff;
            }
            .topnav .avatar {
                border: 1px solid rgba(255,215,0,0.28);
                box-shadow: 0 6px 16px rgba(0,0,0,0.35);
            }
            [x-cloak] {
                display: none !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased theme-dark">
        <div class="min-h-screen flex flex-col" style="backdrop-filter: blur(2px);">
            <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('layout.navigation', []);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2662394211-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

            <!-- Page Heading -->
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <?php echo e($header); ?>

                    </div>
                </header>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <!-- Page Content -->
            <main class="flex-1">
                <div class="section">
                    <div class="container">
                        <?php if (! empty(trim($__env->yieldContent('content')))): ?>
                            <?php echo $__env->yieldContent('content'); ?>
                        <?php elseif(isset($slot)): ?>
                            <?php echo e($slot); ?>

                        <?php else: ?>
                            
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
        <script>
            (function () {
                const body = document.body;
                const stored = localStorage.getItem('ff-theme');
                const mode = stored === 'light' ? 'light' : 'dark';
                body.classList.remove('theme-dark', 'theme-light');
                body.classList.add(mode === 'light' ? 'theme-light' : 'theme-dark');
                body.dataset.mode = mode;
            })();
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js" integrity="sha512-Cj2aqk8VnKXuX4nHCqB6f+GO6zkRgZNpmjDoE7YQDdyCjTiMQuuLHfoalGoVYLRNvKcJste19h9Up7ZK9C1w4g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </body>
</html>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/layouts/app.blade.php ENDPATH**/ ?>