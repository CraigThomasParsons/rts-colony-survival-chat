<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Settings</title>
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
            width: min(560px, 100%);
            background: rgba(11, 14, 26, 0.95);
            border-radius: 18px;
            padding: 1.75rem;
            box-shadow: 0 20px 70px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        h1 { margin: 0 0 1rem; text-align: center; }
        .grid { display: grid; gap: 1rem; }
        label { font-size: 0.9rem; color: #cdd7ff; display: block; margin-bottom: 0.35rem; }
        input, select {
            width: 100%;
            padding: 0.75rem 0.85rem;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.06);
            color: inherit;
        }
        input:focus, select:focus { outline: 2px solid rgba(147, 197, 253, 0.55); }
        button {
            border: none;
            border-radius: 999px;
            padding: 0.85rem 1.5rem;
            font-weight: 600;
            font-size: 1rem;
            background: linear-gradient(120deg, #6366f1, #8b5cf6);
            color: #fff;
            cursor: pointer;
            box-shadow: 0 12px 25px rgba(99,102,241,0.35);
        }
        .links { margin-top: 1rem; display: flex; gap: 1rem; flex-wrap: wrap; }
        a { color: #9ecbff; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Settings</h1>
        <form class="grid">
            <div>
                <label for="music">Music Volume</label>
                <input id="music" type="range" min="0" max="100" value="70">
            </div>
            <div>
                <label for="sfx">SFX Volume</label>
                <input id="sfx" type="range" min="0" max="100" value="70">
            </div>
            <div>
                <label for="themeToggle">Theme</label>
                <div style="display:flex; gap:0.75rem; align-items:center;">
                    <button type="button" id="themeToggle">Toggle Light / Dark</button>
                    <span id="themeStatus" style="font-size:0.85rem;color:#aeb4d6;">Mode: dark</span>
                </div>
            </div>
            <div>
                <label for="tips">Show Tooltips</label>
                <select id="tips">
                    <option>Enabled</option>
                    <option>Disabled</option>
                </select>
            </div>
            <div style="text-align:center;">
                <button type="button">Save (placeholder)</button>
            </div>
        </form>
        <div class="links">
            <a href="{{ route('main.entrance') }}">Main Menu</a>
            <a href="{{ route('control-panel') }}">Control Panel</a>
        </div>
    </div>
    <script>
        (function () {
            const body = document.body;
            const btn = document.getElementById('themeToggle');
            const status = document.getElementById('themeStatus');

            function setMode(mode) {
                if (mode === 'light') {
                    body.classList.add('light-mode');
                    status.textContent = 'Mode: light';
                } else {
                    body.classList.remove('light-mode');
                    status.textContent = 'Mode: dark';
                }
                body.dataset.mode = mode;
                localStorage.setItem('ff-theme', mode);
            }

            const stored = localStorage.getItem('ff-theme') === 'light' ? 'light' : 'dark';
            setMode(stored);

            btn.addEventListener('click', () => {
                const next = body.dataset.mode === 'dark' ? 'light' : 'dark';
                setMode(next);
            });
        })();
    </script>
</body>
</html>
