<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Map Generation — Map #<?php echo e($map->id); ?></title>
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
    <?php
        $mapStatus = $map->status;

        // Simple lifecycle-based progress estimate (works even when jobs are stalled).
        // We keep it deterministic and lightweight for Blade-only rendering.
        $progressPct = match ($mapStatus) {
            'generating' => 25,
            'validating' => 85,
            'ready' => 100,
            'active' => 100,
            'failed' => 100,
            default => 0,
        };
        $progressLabel = match ($mapStatus) {
            'generating' => 'Generating',
            'validating' => 'Validating',
            'ready' => 'Ready',
            'active' => 'Active',
            'failed' => 'Failed',
            default => 'Idle',
        };

        // JS will replace this with log-derived progress when available.
        $progressEndpoint = route('game.mapgen.progress.json', ['mapId' => $map->id]);
    ?>
    <div class="panel">
        <div>
            <h1>Map Generation — Map #<?php echo e($map->id); ?></h1>
            <p class="subtitle">
                Kick off the automated generation pipeline (seed now auto-randomized). We’ll queue artisan steps and stream logs on the next page.
            </p>
        </div>

        <div class="grid">
            <div class="stat">
                <span>Game</span>
                <strong><?php echo e($map->name ?? 'Untitled Map'); ?></strong>
            </div>
            <div class="stat">
                <span>Width</span>
                <strong><?php echo e($map->coordinateX ?? '—'); ?></strong>
            </div>
            <div class="stat">
                <span>Height</span>
                <strong><?php echo e($map->coordinateY ?? '—'); ?></strong>
            </div>
        </div>

        <div class="card">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
                <div style="margin-bottom:1rem;padding:0.9rem 1rem;border-radius:12px;background:rgba(16,185,129,0.12);border:1px solid rgba(16,185,129,0.35);color:#a7f3d0;">
                    <?php echo e(session('status')); ?>

                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div style="margin-bottom:1rem;padding:0.85rem 1rem;border-radius:12px;background:rgba(59,130,246,0.08);border:1px solid rgba(59,130,246,0.25);color:#c7d6ff;">
                Lifecycle status: <strong><?php echo e($mapStatus ?? '—'); ?></strong>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mapStatus === 'failed' && !empty($map->validation_errors)): ?>
                    <div style="margin-top:0.5rem;color:#fecaca; font-size:0.9rem;">
                        Validation errors:
                        <pre style="white-space:pre-wrap; margin:0.35rem 0 0; padding:0.6rem; border-radius:10px; background:rgba(0,0,0,0.35); border:1px solid rgba(255,255,255,0.06);"><?php echo e(is_string($map->validation_errors) ? $map->validation_errors : json_encode($map->validation_errors, JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div style="display:flex; flex-direction:column; gap:1.25rem;">
                <form method="POST" action="<?php echo e(route('game.mapgen.start', ['mapId' => $map->id])); ?>" style="display:flex; flex-direction:column; gap:1.5rem;">
                    <?php echo csrf_field(); ?>

                    

                    <div style="display:flex; flex-wrap:wrap; gap:0.8rem; align-items:center;">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($mapStatus, ['generating', 'validating'], true)): ?>
                            <button type="submit" class="primary" disabled style="opacity:0.6; cursor:not-allowed;">Map is <?php echo e($mapStatus); ?>…</button>
                        <?php else: ?>
                            <button type="submit" class="primary">Start Map Generation</button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <a href="<?php echo e(route('game.mapgen.progress', ['mapId' => $map->id])); ?>" class="secondary" style="text-decoration:none; display:inline-flex; align-items:center;">
                            View Progress
                        </a>

                        
                        <div
                            id="mapgenProgress"
                            aria-label="Generation progress"
                            title="<?php echo e($progressLabel); ?>"
                            data-progress-endpoint="<?php echo e($progressEndpoint); ?>"
                            data-initial-percent="<?php echo e($progressPct); ?>"
                            data-is-active="<?php echo e(in_array($mapStatus, ['generating', 'validating'], true) ? '1' : '0'); ?>"
                            style="display:flex; align-items:center; gap:0.55rem; min-width: 260px;"
                        >
                            <div style="flex: 1; height: 10px; border-radius: 999px; background: rgba(148, 163, 184, 0.18); border: 1px solid rgba(148, 163, 184, 0.22); overflow:hidden;">
                                <div id="mapgenProgressBar" style="width: <?php echo e($progressPct); ?>%; height: 100%; border-radius: 999px; background: linear-gradient(90deg, rgba(99,102,241,0.95), rgba(139,92,246,0.95)); transition: width 300ms ease;"></div>
                            </div>
                            <div id="mapgenProgressText" style="font-size: 0.85rem; color: #cbd5f5; min-width: 92px; text-align:right;">
                                <?php echo e($progressPct); ?>%
                            </div>
                        </div>
                    </div>
                </form>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mapStatus === 'ready'): ?>
                    <form method="POST" action="<?php echo e(isset($gameId) && $gameId ? route('game.start', ['game' => $gameId]) : route('maps.start', ['map' => $map->id])); ?>" style="display:flex; gap:0.8rem; flex-wrap:wrap;">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="primary" style="background:linear-gradient(120deg, #22c55e, #16a34a); box-shadow:0 15px 35px rgba(34,197,94,0.25);">Start Game</button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        <div class="card" style="gap:1rem;">
            <h2 style="margin:0;font-size:1.1rem;">What happens next</h2>
            <ol>
                <li>We queue the artisan steps (<code>map:1init</code> … <code>map:4water</code>) so they run sequentially.</li>
                <li>Logs stream into <code>storage/logs/mapgen-<?php echo e($map->id); ?>.log</code>.</li>
                <li>If anything fails, you can re-run individual steps from the developer map tools.</li>
            </ol>
        </div>

        <div class="footer-links">
            <a class="primary" href="<?php echo e(route('main.entrance')); ?>">Main Menu</a>
            <a href="<?php echo e(route('game.load')); ?>">Load Game</a>
            <a href="<?php echo e(route('control-panel')); ?>">Control Panel</a>
        </div>

        <script>
            (function () {
                const root = document.getElementById('mapgenProgress');
                if (!root) return;

                const endpoint = root.getAttribute('data-progress-endpoint');
                const isActive = root.getAttribute('data-is-active') === '1';
                const bar = document.getElementById('mapgenProgressBar');
                const text = document.getElementById('mapgenProgressText');

                if (!endpoint || !bar || !text) return;

                let lastPercent = Number(root.getAttribute('data-initial-percent') || '0');
                let consecutiveErrors = 0;

                function setProgress(percent, label) {
                    const clamped = Math.max(0, Math.min(100, Number(percent || 0)));
                    if (Number.isFinite(clamped)) {
                        lastPercent = clamped;
                        bar.style.width = clamped + '%';
                        text.textContent = label ? `${clamped}% ${label}` : `${clamped}%`;
                        root.title = label ? label : root.title;
                    }
                }

                async function tick() {
                    try {
                        const res = await fetch(endpoint, {
                            headers: {
                                'Accept': 'application/json'
                            },
                            cache: 'no-store'
                        });

                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const data = await res.json();

                        consecutiveErrors = 0;

                        if (!data || data.ok !== true) {
                            setProgress(lastPercent, ' (progress unavailable)');
                            return;
                        }

                        const pct = Number(data.percent ?? lastPercent);
                        const completed = Number(data.completed ?? 0);
                        const total = Number(data.total ?? 0);

                        let label = '';
                        if (data.exists === false) {
                            label = '(waiting for log…)';
                        } else if (total > 0) {
                            label = `(${completed}/${total})`;
                        }

                        // If the backend says we’re in a terminal state, stop polling.
                        const status = String(data.mapStatus || '').toLowerCase();
                        setProgress(pct, label);

                        if (['ready', 'active', 'failed'].includes(status)) {
                            return; // stop
                        }
                    } catch (e) {
                        consecutiveErrors++;
                        // Back off labeling if transient.
                        if (consecutiveErrors >= 3) {
                            setProgress(lastPercent, '(polling error)');
                        }
                    }

                    setTimeout(tick, 2000);
                }

                // Only poll while generating/validating (keeps it lightweight).
                if (isActive) {
                    setTimeout(tick, 250);
                }
            })();
        </script>
    </div>
</body>
</html>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/game/mapgen.blade.php ENDPATH**/ ?>