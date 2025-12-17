$(function(){
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $editors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $editor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php echo e(config('datatables-html.namespace', 'LaravelDataTables')); ?>["%1$s-<?php echo e($editor->instance); ?>"].on('preSubmit', function(e, data, action) {
            if (action !== 'remove') return;

            for (let row_id of Object.keys(data.data))
            {
                data.data[row_id] = {
                    DT_RowId: data.data[row_id].DT_RowId
                };
            }
        });
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
});
<?php /**PATH /home/craigpar/Code/rts-colony-chat/vendor/yajra/laravel-datatables-html/src/resources/views/functions/batch_remove.blade.php ENDPATH**/ ?>