<?php
    // Keep markup as a <td> so TerrainMap can render a pure table.
    // The emoji is also duplicated into a data attribute to make it easy for JS/hover tooling.
    $mx = $tile['map_x'] ?? 0;
    $my = $tile['map_y'] ?? 0;
    $type = $tile['type'] ?? null;
    $hasTrees = (bool)($tile['has_trees'] ?? false);
    $emoji = $this->treeEmoji;
?>

<td
    class="<?php echo e($this->cssClass); ?> w-4 h-4 text-center align-middle select-none"
    style="width:16px;height:16px;line-height:16px;font-size:12px;border:1px solid rgba(255,255,255,0.04);"
    data-map-x="<?php echo e($mx); ?>"
    data-map-y="<?php echo e($my); ?>"
    data-tile-type="<?php echo e($type !== null ? (int)$type : ''); ?>"
    data-has-trees="<?php echo e($hasTrees ? '1' : '0'); ?>"
    data-tree-emoji="<?php echo e($emoji); ?>"
    title="(<?php echo e($mx); ?>,<?php echo e($my); ?>) type=<?php echo e($type !== null ? (int)$type : '-'); ?><?php echo e($hasTrees ? ' trees' : ''); ?>">
    <?php echo e($emoji); ?>

</td><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/livewire/tile.blade.php ENDPATH**/ ?>