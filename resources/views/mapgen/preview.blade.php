<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Map Preview :: Step 3</title>
    <style>
      :root { color-scheme: dark; }
      body { background: #0b0e1a; color: #e5e7f2; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
      table { border-collapse: collapse; margin: 1rem auto; background: #000; }
      td { width: 20px; height: 20px; padding: 0; }
      .landTile { background-color: #2E8A2E; }
      .treeTile { background-color: #003300; border-bottom: 5px solid #533118; }
      .waterTile { background-color: #003366; }
      .rockTile { background-color: #C0C0C0; }
      .toolbar { display:flex; gap:.75rem; justify-content:center; margin: 1rem 0; }
      a.btn { display:inline-block; padding:.5rem .9rem; border-radius:999px; text-decoration:none; color:#fff; background:#6366f1; }
      a.btn.secondary { background:#374151; }
    </style>
  </head>
  <body>
    <h2 style="text-align:center;">Preview for Map #{{ $map->id }}</h2>

    <table class="Preview">
      @for ($y = ($size * 2); $y > -1; $y-=1)
        @if (isset($tiles[$y]))
          <tr>
            @for ($x = 0; $x < ($size * 2); $x += 1)
              @php $tileExists = isset($tiles[$y]) && isset($tiles[$y][$x]) && $tiles[$y][$x] !== null; @endphp
              @if ($tileExists)
                <td title="{{$tiles[$y][$x]->mapCoordinateX}},{{$tiles[$y][$x]->mapCoordinateY}}-{{$tiles[$y][$x]->tileTypeId}}" class='@include('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]))'>
                </td>
              @else
                <td class="waterTile"></td>
              @endif
            @endfor
          </tr>
        @else
          <tr>
            @for ($x = 0; $x < ($size * 2); $x += 1)
              <td class="waterTile"></td>
            @endfor
          </tr>
        @endif
      @endfor
    </table>

    <div class="toolbar">
      <a class="btn" href="{{ $nextRoute }}">Next Step</a>
      <a class="btn secondary" href="{{ url('/load-game') }}">Back to Load Games</a>
    </div>
  </body>
</html>
