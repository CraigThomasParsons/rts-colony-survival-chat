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
            height: 20px;
            width: 20px;
        }

        .landTile {
          background-color: #2E8A2E;
          border-color: #996600;
          border-style: solid;
          border-width: 0px;
        }
        .treeTile {
          background-color: #003300;
          border-color: #006600;
          border-bottom-style: solid;
          border-bottom-width: 5px;
          border-bottom-color: #533118;
        }
        .waterTile {
          background-color: #003366;
          border-color: #0A0A0A;
        }
        .rockTile {
          background-color: #C0C0C0;
          border-color: #606060;
        }

        .leftEdge {
          border-left-style: outset;
        }
        .rightEdge {
          border-right-style: outset;
        }
        .topEdge {
          border-top-style: outset;
        }
        .bottomEdge {
          border-bottom-style: outset;
        }
        .tileTopRight {
            border-top-style: outset;
            border-right-style: outset;
        }
        .tileTopLeft {
            border-top-style: outset;
            border-left-style: outset;
        }
        .tileBottomLeft {
            border-bottom-style: outset;
            border-left-style: outset;
        }
        .tileBottomRight {
            border-right-style: outset;
            border-bottom-style: outset;
        }
        .TopRightConcaveCorner {
            border-right-style: double;
            border-top-style: double;
        }
        .TopLeftConcaveCorner {
            border-top-style: double;
            border-left-style: double;
        }
        .bottomRightConcaveCorner {
            border-right-style: double;
            border-bottom-style: double;
        }
        .bottomLeftConcaveCorner {
            border-left-style: double;
            border-bottom-style: double;
        }
      </style>
    </head>
    <body>
<?php
/**
 * This view has to go through the arrays from the top down.
 */
?>
      <br />
      <table class="Preview">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($y = ($size * 2); $y > -1; $y-=1): ?>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($tiles[$y])): ?>
            <tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($x = 0; $x < ($size * 2); $x += 1): ?>
                <?php
                  $tileExists = isset($tiles[$y]) && isset($tiles[$y][$x]) && $tiles[$y][$x] !== null;
                ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tileExists): ?>
                  <td title="<?php echo e($tiles[$y][$x]->mapCoordinateX); ?>,<?php echo e($tiles[$y][$x]->mapCoordinateY); ?>-<?php echo e($tiles[$y][$x]->tileTypeId); ?>" class='<?php echo $__env->make('mapgen.tiletypeclassname', array('tile' => $tiles[$y][$x]), array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>'>
                    <div>&nbsp;</div>
                  </td>
                <?php else: ?>
                  <td class="waterTile">
                    <div>&nbsp;</div>
                  </td>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tr>
          <?php else: ?>
            <tr>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($x = 0; $x < ($size * 2); $x += 1): ?>
                <td class="waterTile"><div>&nbsp;</div></td>
              <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
      </table>
    <div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($next)): ?>
            <a href="<?php echo e(URL::route($next, ['mapId' => request()->route('mapId')])); ?>">
          Next Step
        </a>
    <?php else: ?>
        <a href="<?php echo e(route('mapgen.preview', ['mapId' => request()->route('mapId') ?? 1])); ?>">
          Next Step
        </a>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
  </body>
</html>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mapgen/mapload.blade.php ENDPATH**/ ?>