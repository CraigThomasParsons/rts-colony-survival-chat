 <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tile->tileTypeId == 1): ?>
landTile
 <?php elseif($tile->tileTypeId == 2): ?>
rockTile
 <?php elseif($tile->tileTypeId == 3): ?>
waterTile
<?php elseif($tile->tileTypeId == 4): ?>

rockTile tileTopRight

<?php elseif($tile->tileTypeId == 5): ?>

rockTile tileBottomLeft

<?php elseif($tile->tileTypeId == 6): ?>

rockTile tileTopLeft

 <?php elseif($tile->tileTypeId == 7): ?>
rockTile tileBottomRight

 <?php elseif($tile->tileTypeId == 8): ?>
TopRightConcaveCorner rockTile

 <?php elseif($tile->tileTypeId == 9): ?>
TopLeftConcaveCorner rockTile

 <?php elseif($tile->tileTypeId == 10): ?>
bottomRightConcaveCorner rockTile

 <?php elseif($tile->tileTypeId == 11): ?>
bottomLeftConcaveCorner rockTile

 <?php elseif($tile->tileTypeId == 12): ?>
topEdge rockTile

 <?php elseif($tile->tileTypeId == 13): ?>
rightEdge rockTile

 <?php elseif($tile->tileTypeId == 14): ?>
bottomEdge rockTile

 <?php elseif($tile->tileTypeId == 15): ?>
leftEdge rockTile


 <?php elseif($tile->tileTypeId == 16): ?>
waterTile tileTopRight


 <?php elseif($tile->tileTypeId == 17): ?>
waterTile tileBottomLeft


 <?php elseif($tile->tileTypeId == 18): ?>
waterTile tileTopLeft


 <?php elseif($tile->tileTypeId == 19): ?>
waterTile tileBottomRight


 <?php elseif($tile->tileTypeId == 20): ?>
TopRightConcaveCorner waterTile


 <?php elseif($tile->tileTypeId == 21): ?>
TopLeftConcaveCorner waterTile


 <?php elseif($tile->tileTypeId == 22): ?>
bottomRightConcaveCorner waterTile


 <?php elseif($tile->tileTypeId == 23): ?>
bottomLeftConcaveCorner waterTile


 <?php elseif($tile->tileTypeId == 24): ?>
topEdge waterTile


 <?php elseif($tile->tileTypeId == 25): ?>
rightEdge waterTile


 <?php elseif($tile->tileTypeId == 26): ?>
bottomEdge waterTile


 <?php elseif($tile->tileTypeId == 27): ?>
leftEdge waterTile

 <?php else: ?>
treeTile
 <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
<?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mapgen/tiletypeclassname.blade.php ENDPATH**/ ?>