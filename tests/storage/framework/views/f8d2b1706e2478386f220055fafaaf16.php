<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto py-8 space-y-8">
    <h1 class="text-2xl font-semibold">Games</h1>

    <div class="bg-gray-800/60 p-4 rounded">
        <h2 class="text-lg font-medium mb-3">Create New Game</h2>
        <form method="POST" action="<?php echo e(route('game.create')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-sm mb-1" for="name">Name</label>
                <input name="name" id="name" required class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm" placeholder="My Colony" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1" for="width">Width</label>
                    <input name="width" id="width" type="number" min="32" max="128" value="64" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm" />
                </div>
                <div>
                    <label class="block text-sm mb-1" for="height">Height</label>
                    <input name="height" id="height" type="number" min="32" max="128" value="64" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm" />
                </div>
            </div>
            <button class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 rounded text-sm font-medium">Create & Generate</button>
        </form>
    </div>

    <div>
        <h2 class="text-lg font-medium mb-3">Recent Games</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm border border-gray-700">
                <thead class="bg-gray-800">
                    <tr>
                        <th class="px-3 py-2 text-left">Name</th>
                        <th class="px-3 py-2 text-left">Maps</th>
                        <th class="px-3 py-2 text-left">Created</th>
                        <th class="px-3 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $games; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $game): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="border-t border-gray-700">
                        <td class="px-3 py-2"><?php echo e($game->name); ?></td>
                        <td class="px-3 py-2">
                            <?php echo e($game->maps->count()); ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($game->maps->first()): ?>
                                <div class="text-xs text-gray-400">status: <?php echo e($game->maps->first()->status ?? '—'); ?></div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                        <td class="px-3 py-2"><?php echo e($game->created_at?->diffForHumans()); ?></td>
                        <td class="px-3 py-2 space-x-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($game->maps->first()): ?>
                                <?php ($m = $game->maps->first()); ?>
                                <a href="<?php echo e(route('map.editor', ['mapId' => $m->id])); ?>" class="text-blue-400 hover:text-blue-300 underline">Editor</a>
                                <a href="<?php echo e(route('game.mapgen.form', ['mapId' => $game->maps->first()->id])); ?>" class="text-indigo-400 hover:text-indigo-300 underline">Generate</a>
                                <a href="<?php echo e(route('mapgen.preview', ['mapId' => $game->maps->first()->id])); ?>" class="text-green-400 hover:text-green-300 underline">Preview</a>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(($m->status ?? null) === 'ready'): ?>
                                    <form method="POST" action="<?php echo e(route('game.start', ['game' => $game->id])); ?>" class="inline">
                                        <?php echo csrf_field(); ?>
                                        <button type="submit" class="text-emerald-400 hover:text-emerald-300 underline">Start Game</button>
                                    </form>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-gray-500">No games yet. Create one above.</td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/game/index.blade.php ENDPATH**/ ?>