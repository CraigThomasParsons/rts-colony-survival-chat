<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto">
  <h2 class="text-xl font-semibold mb-4">Heightmap Preview</h2>
  <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('heightmap-preview', ['mapId' => $map->id]);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2981563153-0', null);

$__html = app('livewire')->mount($__name, $__params, $key);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>

  <div class="mt-4 flex gap-3">
    <a href="<?php echo e(route('map.tilemap.preview', ['mapId' => $map->id])); ?>" class="inline-block px-4 py-2 bg-indigo-600 text-white rounded">Tile Map</a>
    <a href="<?php echo e($nextRoute); ?>" class="inline-block px-4 py-2 bg-blue-600 text-white rounded">Run Step 2</a>
  </div>

  <h3 class="text-lg font-semibold mt-6 mb-2">Heightmap Table</h3>
  <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('heightmap-table', ['mapId' => $map->id]);

$key = null;

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2981563153-1', null);

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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/mapgen/heightmap-preview.blade.php ENDPATH**/ ?>