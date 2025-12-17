<?php if (! $__env->hasRenderedOnce('1b8ca543-8b2f-4515-9f10-c3ac663d474f')): $__env->markAsRenderedOnce('1b8ca543-8b2f-4515-9f10-c3ac663d474f'); ?>
    <style>
        .map-preview-grid {
            display: grid;
            gap: 2px;
            padding: 12px;
            border-radius: 0.75rem;
            background: radial-gradient(circle at top, rgba(15, 23, 42, 0.95), rgba(2, 6, 23, 0.85));
            box-shadow: inset 0 0 15px rgba(0, 0, 0, 0.45);
            grid-auto-rows: 18px;
        }

        .map-preview-tile {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 4px;
            background-size: cover;
            background-position: center;
            transition: opacity 0.2s ease;
        }

        .map-preview-tile:hover {
            opacity: 0.85;
        }

        .tile-water {
            background-image: linear-gradient(145deg, #0ea5e9, #0369a1);
        }

        .tile-sand {
            background-image: linear-gradient(145deg, #fcd34d, #f59e0b);
        }

        .tile-grass {
            background-image: linear-gradient(145deg, #4ade80, #15803d);
        }

        .tile-forest {
            background-image: linear-gradient(145deg, #065f46, #064e3b);
        }

        .tile-hill {
            background-image: linear-gradient(145deg, #9ca3af, #475569);
        }

        .tile-unknown {
            background-image: linear-gradient(145deg, #94a3b8, #64748b);
        }
    </style>
<?php endif; ?>

<div class="space-y-48">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Surface Map Preview</h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            Adjust the dimensions and click “Generate” to see a real-time grid preview. Each tile is rendered as a div so we can swap to an actual sprite sheet later. (Seed removed; generation now auto-randomizes internally.)
        </p>
    </div>

    <form method="POST" action="<?php echo e(route('map.generate')); ?>" class="grid gap-20 sm:grid-cols-4" x-data="{ working:false }">
        <?php echo csrf_field(); ?>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Width</label>
            <input type="number" name="width" min="16" max="96" wire:model="width"
                   placeholder="64"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700" />
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['width'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="col-span-full"><br></div>
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200">Height</label>
            <input type="number" name="height" min="16" max="96" wire:model="height"
                   placeholder="38"
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700" />
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['height'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-sm text-red-500"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <div class="col-span-full"><br></div>
        
        <div class="col-span-full"><br></div>
        <div class="flex flex-wrap items-center gap-10 sm:col-span-4">
            <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Generate
            </button>
        </div>
    </form>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($grid): ?>
        <div class="space-y-16">
            <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-300">
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $palette; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span><span class="font-semibold"><?php echo e($meta['label']); ?>:</span> <?php echo e($counts[$key] ?? 0); ?></span>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            <div class="overflow-auto">
                <div class="map-preview-grid"
                     style="grid-template-columns: repeat(<?php echo e($width); ?>, 18px); margin-top: 4rem;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $grid; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($tile = $palette[$cell] ?? ['label' => 'Unknown', 'class' => 'tile-unknown']); ?>
                            <div class="map-preview-tile <?php echo e($tile['class']); ?>" title="<?php echo e($tile['label']); ?>"></div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            <div class="flex flex-wrap gap-4 text-sm text-gray-600 dark:text-gray-400">
                <div class="font-semibold">Legend:</div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $palette; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-1">
                        <span class="inline-block h-4 w-4 rounded <?php echo e($meta['class']); ?>"></span>
                        <?php echo e($meta['label']); ?>

                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Run a preview to visualize the current surface generation parameters.
        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/livewire/map-generator-preview.blade.php ENDPATH**/ ?>