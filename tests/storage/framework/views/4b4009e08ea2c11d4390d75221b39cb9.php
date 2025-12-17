<!doctype html>
<html>
    <head>
      <meta charset="utf-8">
      <title>Map Loader :: Step 1</title>
      <style>
        table {
            width: 30px;
            border: 1px solid black;
        }
        td {
            text-align: center;
            valign: center;
        }
        table td > div {
            overflow: hidden;
            height: 10px;
            width: 10px;
        }
      </style>
    </head>
    <body>
<?php
/**
 * This view has to go through the arrays from the top down.
 */
?>
      <table border="1" class="Preview">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($y = $size - 1; $y > -1; $y--): ?>
        <tr>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($x = 0; $x < $size; $x += 1): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($cells[$x][$y])): ?> 
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($cells[$x][$y]->name == 'Passable Land'): ?>
                <td bgcolor="#ffffcc" valign="middle">
                  <div>
                  </div>
                </td>
              <?php elseif($cells[$x][$y]->name == 'Trees'): ?>
                <td bgcolor="#006600" valign="middle">
                  <div>
                  </div>
                </td>
              <?php elseif($cells[$x][$y]->name == 'Water'): ?>
                <td bgcolor="#333399" valign="middle">
                  <div>
                  </div>
                </td>
              <?php else: ?>
                <td bgcolor="#996633">
                  <div>
                  </div>
                </td>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
                <td bgcolor="#996633"><div><?php echo e($x.'-'.$y); ?></div></td>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tr>
      <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </table>
        <br />
        Going to run first step on MapId for id[ <?php echo e($mapId); ?> ]
        <div>
            <a href="<?php echo e(URL::route('mapgen.step2', '1')); ?>">
              Next Step
            </a>
        </div>
    </body>
</html>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mapgen/firststep.blade.php ENDPATH**/ ?>