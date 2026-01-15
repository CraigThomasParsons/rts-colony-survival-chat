<div class="container center">
    <div>
        <input type="text" wire:model="task" wire:keydown.enter="addTodo" placeholder="Add new todo"/>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $todos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $todo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
    <div class="box" :wire:key="$loop->index">
        <input type="checkbox" id='markAsDone-<?php echo e($todo->id); ?>'
               wire:change="markAsDone(<?php echo e($todo->id); ?>)"
        />
        <label for="markAsDone-<?php echo e($todo->id); ?>"
               style="<?php echo e($todo->getTextStyle()); ?>">
            <?php echo e($todo->description); ?>

        </label>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($todo->status == 'done'): ?>
          &nbsp;
          <button wire:click="remove(<?php echo e($todo->id); ?>)">
            delete
          </button>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <br />
   </div>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <p>No Todos</p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH /home/craigpar/Code/rts-colony-chat/resources/views/livewire/todo-list.blade.php ENDPATH**/ ?>