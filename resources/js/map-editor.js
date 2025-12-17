(function(){
  document.addEventListener('DOMContentLoaded', function(){
    const canvas = document.getElementById('mapEditorCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const tileSize = 4;

    let preview = [];
    let drawing = false;

    // receive preview from Livewire
    Livewire.on('mapPreview', (matrix) => {
      preview = matrix;
      drawPreview();
    });

    function drawPreview() {
      if (!preview || !preview.length) {
        ctx.fillStyle = '#222';
        ctx.fillRect(0,0,canvas.width,canvas.height);
        return;
      }
      for (let y=0;y<preview.length;y++) {
        for (let x=0;x<preview[y].length;x++) {
          const rgb = preview[y][x];
          let color = 'rgb(120,180,90)';
          if (rgb) color = `rgb(${rgb[0]},${rgb[1]},${rgb[2]})`;
          ctx.fillStyle = color;
          ctx.fillRect(x*tileSize, y*tileSize, tileSize, tileSize);
        }
      }
    }

    // painting handlers
    canvas.addEventListener('mousedown', (e) => { drawing = true; handlePaint(e); });
    canvas.addEventListener('mouseup', () => { drawing = false; });
    canvas.addEventListener('mouseleave', () => { drawing = false; });
    canvas.addEventListener('mousemove', (e) => { if (drawing) handlePaint(e); });

    function handlePaint(e) {
      const rect = canvas.getBoundingClientRect();
      const x = Math.floor((e.clientX - rect.left) / tileSize);
      const y = Math.floor((e.clientY - rect.top) / tileSize);
      const sel = document.getElementById('brushSelect').value;
      const parts = sel.split(':');
      const kind = parts[0];
      const val = parts[1];
      let terrain = null, resource = null;
      if (kind === 'terrain') terrain = val;
      if (kind === 'resource') {
        if (val !== 'none') resource = val;
      }

      // send livewire event
      Livewire.emit('paintTile', {x: x, y: y, terrain: terrain, resource: resource});
    }

    // place settlements
    const placeBtn = document.getElementById('placeSettlementBtn');
    if (placeBtn) {
      placeBtn.addEventListener('click', () => {
        fetch('/admin/mapgen/place-settlements', {
          method:'POST',
          headers:{
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          }
        })
        .then(r=>r.json())
        .then(j=>{ alert('Placed ' + (j.count || 0) + ' settlements'); Livewire.emit('requestPreview'); });
      });
    }
  });
})();
