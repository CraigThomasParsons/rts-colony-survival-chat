<?php $__env->startSection('content'); ?>
<div class="container p-6">
    <h1 class="text-2xl font-bold mb-4">Map Generation Admin</h1>

    <div class="space-y-4">
        <h2 class="font-semibold">Generator Preview</h2>
        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('mapgen-preview', []);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-4052291193-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
    </div>

    <div class="mt-6">
        <h2 class="font-semibold">Editor</h2>
        <a href="<?php echo e(route('admin.mapgen.editor')); ?>" class="text-blue-600">Open Map Editor</a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/admin/mapgen/index.blade.php ENDPATH**/ ?>