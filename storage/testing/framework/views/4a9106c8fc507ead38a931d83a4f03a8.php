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
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

        <style>
            body {
                font-family: 'Figtree', sans-serif;
            }
            body.theme-dark {
                background: linear-gradient(180deg, rgba(6,11,25,0.95), rgba(3,3,12,0.9)), url('<?php echo e(asset('images/login-bg.png')); ?>') center/cover fixed;
                color: #f4f6ff;
            }
            body.theme-light {
                background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(219,233,255,0.85)), url('<?php echo e(asset('images/login-bg.png')); ?>') center/cover fixed;
                color: #1f2937;
            }
        </style>
    </head>
    <body class="theme-dark">
        <div class="valign-wrapper" style="min-height:100vh;">
            <div class="container">
                <div class="row">
                    <div class="col s12 m10 l6 offset-m1 offset-l3">
                        <div class="card" style="border-radius:24px; background: rgba(15,18,36,0.92); box-shadow:0 25px 70px rgba(0,0,0,0.55); border:1px solid rgba(255,255,255,0.05);">
                            <div class="card-content">
                                <?php echo e($slot); ?>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
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
        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js" integrity="sha512-Cj2aqk8VnKXuX4nHCqB6f+GO6zkRgZNpmjDoE7YQDdyCjTiMQuuLHfoalGoVYLRNvKcJste19h9Up7ZK9C1w4g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    </body>
</html>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/layouts/guest.blade.php ENDPATH**/ ?>