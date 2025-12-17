<!doctype html>
<html>
    <head>
      <meta charset="utf-8">
      <title>Map Loader :: Step 2</title>
      <style>
        table {
            width: 180px;
            border: 2px outset black;
            background-color: black;
            border-color: black;
        }
        td {
            text-align: center;
            valign: center;
            border-width: medium;
            background-color: #003366;
            border-color: #996600;
        }
        table td > div {
            overflow: hidden;
            height: 12px;
            width: 12px;
        }

        .landCell {
          border-color: #996600;
        }
        .treeCell {
          border-color: #006600;
        }
        .waterCell {
          border-color: #0A0A0A;
        }
        .rockCell {
          border-color: #606060;
        }

        .landTile {
          background-color: #2E8A2E;
        }
        .treeTile {
          background-color: #003300;
        }
        .waterTile {
          background-color: #003366;
        }
        .rockTile {
          background-color: #C0C0C0;
        }

        .tileTopLeft {
            border-top-style: outset;
            border-right-style: double;
            border-bottom-style: double;
            border-left-style: outset;
        }
        .tileBottomLeft {
            border-top-style: double;
            border-right-style: double;
            border-bottom-style: outset;
            border-left-style: outset;
        }
        .tileTopRight {
            border-top-style: outset;
            border-right-style: outset;
            border-bottom-style: double;
            border-left-style: double;
        }
        .tileBottomRight {
            border-top-style: double;
            border-right-style: outset;
            border-bottom-style: outset;
            border-left-style: double;
        }
      </style>
    </head>
    <body>
<?php
/**
 * This view has to go through the arrays from the top down.
 */
?>
      Going to run second step on MapId for id[ <?php echo e($mapId); ?> ]
      <br />
      <table class="Preview">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($y = ($size * 2); $y > -1; $y-=2): ?>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($offset = 0; $offset < 2; $offset+=1): ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($y + $offset) == $y): ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($tiles[$y])): ?>
                <tr>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($x = 0; $x < 100; $x += 1): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($tiles[$y][$x])): ?>
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tiles[$y][$x]->coordinateX == 1): ?>
                        <td title="<?php echo e($tiles[$y][$x]->mapCoordinateX); ?>,<?php echo e($tiles[$y][$x]->mapCoordinateY); ?>"
                        class='tileTopRight <?php echo $__env->make('mapgen.tileclassname', array('x' => $x, 'y' => $y, 'cells' => $cells), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php echo $__env->make('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>'>
                          <div>&nbsp;</div>
                        </td>
                      <?php else: ?>
                        <td title="<?php echo e($tiles[$y][$x]->mapCoordinateX); ?>,<?php echo e($tiles[$y][$x]->mapCoordinateY); ?>"
                        class='tileTopLeft <?php echo $__env->make('mapgen.tileclassname', array('x' => $x, 'y' => $y, 'cells' => $cells), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php echo $__env->make('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>'>
                          <div>&nbsp;</div>
                        </td>
                      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($tiles[$y + $offset])): ?>
                <tr>
                  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($x = 0; $x < 100; $x += 1): ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($tiles[$y + $offset][$x])): ?>
                      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tiles[$y][$x]->coordinateX == 1): ?>
                        <td title="<?php echo e($tiles[$y][$x]->mapCoordinateX); ?>,<?php echo e($tiles[$y][$x]->mapCoordinateY); ?>"
                        class='tileBottomRight <?php echo $__env->make('mapgen.tileclassname', array('x' => $x, 'y' => $y, 'cells' => $cells), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php echo $__env->make('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>'>
                          <div>&nbsp;</div>
                        </td>
                      <?php else: ?>
                        <td title="<?php echo e($tiles[$y][$x]->mapCoordinateX); ?>,<?php echo e($tiles[$y][$x]->mapCoordinateY); ?>"
                        class='tileBottomLeft <?php echo $__env->make('mapgen.tileclassname', array('x' => $x, 'y' => $y, 'cells' => $cells), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php echo $__env->make('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>'>
                          <div>&nbsp;</div>
                        </td>
                      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                  <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tr>
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
          <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </table>
    <div>
        <a href="<?php echo e(URL::route('mapgen.step3', '1')); ?>">
          Next Step
        </a>
    </div>
  </body>
</html>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mapgen/secondstep.blade.php ENDPATH**/ ?>