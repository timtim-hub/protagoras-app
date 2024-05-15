<select id="model" name="model" class="form-select" >	
    <option value="gpt-3.5-turbo-0125" <?php if("gpt-3.5-turbo-0125" == $default_model): ?> selected <?php endif; ?>><?php echo e(__('GPT 3.5 Turbo')); ?></option>	
    <?php $__currentLoopData = $models; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $model): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if(trim($model) == 'gpt-4'): ?>
            <option value="<?php echo e(trim($model)); ?>" <?php if(trim($model) == $default_model): ?> selected <?php endif; ?>><?php echo e(__('GPT 4')); ?></option>
        <?php elseif(trim($model) == 'gpt-4-0125-preview'): ?>
            <option value="<?php echo e(trim($model)); ?>" <?php if(trim($model) == $default_model): ?> selected <?php endif; ?>><?php echo e(__('GPT 4 Turbo')); ?></option>
        <?php elseif(trim($model) == 'gpt-4-turbo-2024-04-09'): ?>
            <option value="<?php echo e(trim($model)); ?>" <?php if(trim($model) == $default_model): ?> selected <?php endif; ?>><?php echo e(__('GPT 4 Turbo with Vision')); ?></option>
        <?php elseif(trim($model) == 'claude-3-opus-20240229'): ?>
            <option value="<?php echo e(trim($model)); ?>" <?php if(trim($model) == $default_model): ?> selected <?php endif; ?>><?php echo e(__('Claude 3 Opus')); ?></option>
        <?php elseif(trim($model) == 'claude-3-sonnet-20240229'): ?>
            <option value="<?php echo e(trim($model)); ?>" <?php if(trim($model) == $default_model): ?> selected <?php endif; ?>><?php echo e(__('Claude 3 Sonnet')); ?></option>
        <?php elseif(trim($model) == 'claude-3-haiku-20240307'): ?>
            <option value="<?php echo e(trim($model)); ?>" <?php if(trim($model) == $default_model): ?> selected <?php endif; ?>><?php echo e(__('Claude 3 Haiku')); ?></option>
        <?php elseif(trim($model) == 'gemini_pro'): ?>
            <option value="<?php echo e(trim($model)); ?>" <?php if(trim($model) == $default_model): ?> selected <?php endif; ?>><?php echo e(__('Gemini Pro')); ?></option>
        <?php else: ?>
            <?php $__currentLoopData = $fine_tunes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $fine_tune): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(trim($model) == $fine_tune->model): ?>
                    <option value="<?php echo e($fine_tune->model); ?>" <?php if(trim($model) == $default_model): ?> selected <?php endif; ?>><?php echo e($fine_tune->description); ?></option>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        <?php endif; ?>
        
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>									
</select><?php /**PATH /home/aihowgkq/protagoras.app/resources/views/components/original-template-models.blade.php ENDPATH**/ ?>