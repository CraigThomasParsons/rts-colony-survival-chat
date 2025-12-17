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
    <h2 style="text-align:center;">Preview for Map #<?php echo e($map->id); ?></h2>

    <table class="Preview">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($y = ($size * 2); $y > -1; $y-=1): ?>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($tiles[$y])): ?>
          <tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($x = 0; $x < ($size * 2); $x += 1): ?>
              <?php $tileExists = isset($tiles[$y]) && isset($tiles[$y][$x]) && $tiles[$y][$x] !== null; ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tileExists): ?>
                <td title="<?php echo e($tiles[$y][$x]->mapCoordinateX); ?>,<?php echo e($tiles[$y][$x]->mapCoordinateY); ?>-<?php echo e($tiles[$y][$x]->tileTypeId); ?>" class='<?php echo $__env->make('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>'>
                </td>
              <?php else: ?>
                <td class="waterTile"></td>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tr>
        <?php else: ?>
          <tr>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($x = 0; $x < ($size * 2); $x += 1): ?>
              <td class="waterTile"></td>
            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          </tr>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </table>

    <div class="toolbar">
      <a class="btn" href="<?php echo e($nextRoute); ?>">Next Step</a>
      <a class="btn secondary" href="<?php echo e(url('/load-game')); ?>">Back to Load Games</a>
    </div>
  </body>
</html>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mapgen/preview.blade.php ENDPATH**/ ?>