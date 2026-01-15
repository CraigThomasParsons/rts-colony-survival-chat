<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-6">
    <a href="<?php echo e(route('map.editor', ['mapId' => $mapId])); ?>" class="text-blue-400 hover:underline">Back to Map Editor</a>
    <h1 class="text-3xl font-semibold text-gray-100 mb-4">Tile Map Preview</h1>
    <div id="tileCounts" class="text-sm text-gray-300 mb-3">Counts: <span class="text-gray-400">loading…</span></div>
    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('terrain-map', ['mapId' => $mapId]);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-442362561-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mapgen/tilemap-preview.blade.php ENDPATH**/ ?>