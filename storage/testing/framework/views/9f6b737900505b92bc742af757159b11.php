<div class="flex h-screen bg-gray-900 text-white">
    
    <div class="w-64 p-4 space-y-4 bg-gray-950/80">
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('game-hud');

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-236669297-1', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>

    
    <div class="flex-1 flex items-center justify-center">
        <div id="game-container" wire:ignore></div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (window.startFeudalFrontiersGame) {
            window.startFeudalFrontiersGame('game-container');
        }
    });
</script>
<?php $__env->stopPush(); ?><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/livewire/game-screen.blade.php ENDPATH**/ ?>