<div class="p-4">
  <div class="flex items-center gap-4 mb-2">
    <div>
      <span class="font-semibold">Map:</span>
      <span class="text-sm"><?php echo e($mapId); ?></span>
    </div>
    <div>
      <span class="font-semibold">Grid:</span>
      <span class="text-sm"><?php echo e($sizeX); ?> × <?php echo e($sizeY); ?></span>
    </div>
  </div>

  <div class="border rounded p-2 bg-gray-50">
    <canvas id="heightmapCanvas" class="w-full" style="image-rendering: pixelated;" width="<?php echo e($sizeX); ?>" height="<?php echo e($sizeY); ?>"></canvas>
  </div>

  <script>
    (function() {
      const mapId = JSON.parse('<?php echo json_encode($mapId, 15, 512) ?>');
      const canvas = document.getElementById('heightmapCanvas');
      const ctx = canvas.getContext('2d');

      function drawHeightmap(grid, w, h) {
        const imgData = ctx.createImageData(w, h);
        let i = 0;
        for (let y = 0; y < h; y++) {
          for (let x = 0; x < w; x++) {
            const cell = grid[y]?.[x];
            const val = Math.max(0, Math.min(255, Math.round((cell?.h ?? 0))));
            imgData.data[i++] = val;
            imgData.data[i++] = val;
            imgData.data[i++] = val;
            imgData.data[i++] = 255;
          }
        }
        ctx.putImageData(imgData, 0, 0);
      }

      async function refresh() {
        try {
          const res = await fetch(`/game/` + mapId + `/heightmap-data`);
          if (!res.ok) return;
          const data = await res.json();
          drawHeightmap(data.grid, data.w, data.h);
        } catch (e) {
          console.warn('Heightmap preview fetch failed', e);
        }
      }

      // Initial draw and periodic refresh
      refresh();
      const intervalId = setInterval(refresh, 3000);
      window.addEventListener('beforeunload', () => clearInterval(intervalId));
    })();
  </script>
</div><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/livewire/heightmap-preview.blade.php ENDPATH**/ ?>