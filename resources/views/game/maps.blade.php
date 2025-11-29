<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Game Maps</title>
  <link rel="stylesheet" href="{{ asset('css/panel.css') }}">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <style>
    :root { color-scheme: dark; }
    body { margin:0; }
    h1 { margin:0 0 1rem; }
    .footer { display:flex; gap:.75rem; }
    a.btn { display:inline-block; padding:.6rem 1.1rem; border-radius:999px; text-decoration:none; color:#fff; background:linear-gradient(120deg,#6366f1,#8b5cf6); }
    a.btn.secondary { background: rgba(255,255,255,0.06); border:1px solid rgba(255,255,255,0.08); color:#cdd7ff; }
  </style>
</head>
<body>
  <div class="panel">
    <h1>Maps for Game #{{ $game->id }} — {{ $game->name }}</h1>
    <table id="maps" class="display" style="width:100%">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Description</th>
          <th>Width</th>
          <th>Height</th>
          <th>Created</th>
          <th>Actions</th>
        </tr>
      </thead>
    </table>
    <div class="footer" style="margin-top:1rem;">
      <a class="btn secondary" href="{{ route('game.load') }}">Back to Load Games</a>
      <a class="btn secondary" href="{{ route('main.entrance') }}">Main Menu</a>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script>
    $(function(){
      $('#maps').DataTable({
        processing: true,
        serverSide: false,
        ajax: '{{ route('api.game.maps', ['game' => $game->id]) }}',
        columns: [
          { data: 'id' },
          { data: 'name' },
          { data: 'description' },
          { data: 'coordinateX', title: 'Width' },
          { data: 'coordinateY', title: 'Height' },
          { data: 'created_at' },
          { data: 'actions', orderable:false, searchable:false },
        ]
      });
    });
  </script>
</body>
</html>
