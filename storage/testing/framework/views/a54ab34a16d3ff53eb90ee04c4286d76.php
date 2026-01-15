<?php $__env->startSection('content'); ?>
<div class="p-4">
    <h1 class="text-xl font-semibold mb-4">Feudal Frontiers</h1>
    <div id="phaser-game" class="border border-gray-700 bg-black/40 w-[640px] h-[640px]"></div>
</div>
<script>
    window.addEventListener('DOMContentLoaded', () => {
        if (window.startFeudalFrontiersGame) {
            window.startFeudalFrontiersGame({ width: 640, height: 640, workerFrames: 8, rightClickQueue: true });
        }
    });
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/game/screen.blade.php ENDPATH**/ ?>