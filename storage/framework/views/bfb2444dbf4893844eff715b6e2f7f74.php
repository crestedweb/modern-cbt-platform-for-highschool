

<?php $__env->startSection('title', 'Edit Exam'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow p-8">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Edit Exam</h2>
            <a href="<?php echo e(route('admin.exams')); ?>" class="text-gray-600 hover:text-gray-800">
                ← Back to Exams
            </a>
        </div>

        <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php endif; ?>

        <form action="<?php echo e(route('admin.exam.update', $exam->id)); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Title *</label>
                <input type="text" name="title" value="<?php echo e(old('title', $exam->title)); ?>" 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                       placeholder="e.g., Computer Science Mid-Term Exam" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea name="description" rows="3" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="Brief description of the exam"><?php echo e(old('description', $exam->description)); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Subject *</label>
                    <input type="text" name="subject" value="<?php echo e(old('subject', $exam->subject)); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           placeholder="e.g., Mathematics, English" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Duration (Minutes) *</label>
                    <input type="number" name="duration_minutes" value="<?php echo e(old('duration_minutes', $exam->duration_minutes)); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           min="1" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Total Marks *</label>
                    <input type="number" name="total_marks" value="<?php echo e(old('total_marks', $exam->total_marks)); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           min="1" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pass Mark *</label>
                    <input type="number" name="pass_mark" value="<?php echo e(old('pass_mark', $exam->pass_mark)); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           min="0" required>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Start Date *</label>
                    <input type="datetime-local" name="start_date" 
                           value="<?php echo e(old('start_date', $exam->start_date->format('Y-m-d\TH:i'))); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">End Date *</label>
                    <input type="datetime-local" name="end_date" 
                           value="<?php echo e(old('end_date', $exam->end_date->format('Y-m-d\TH:i'))); ?>" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                           required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Instructions</label>
                <textarea name="instructions" rows="4" 
                          class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                          placeholder="Instructions for students (e.g., Answer all questions, No use of calculators)"><?php echo e(old('instructions', $exam->instructions)); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Assign to Classes *</label>
                <div class="space-y-2 bg-gray-50 p-4 rounded-lg">
                    <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <label class="flex items-center">
                        <input type="checkbox" name="classes[]" value="<?php echo e($class->id); ?>" 
                               <?php echo e(in_array($class->id, old('classes', $exam->classes->pluck('id')->toArray())) ? 'checked' : ''); ?>

                               class="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                        <span class="font-medium"><?php echo e($class->name); ?></span>
                        <span class="text-gray-500 text-sm ml-2">- <?php echo e($class->description); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php $__errorArgs = ['classes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p>
                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                <p class="text-sm text-gray-600 mt-2">
                    ℹ️ You can add or remove classes. Students in newly added classes will be able to take this exam.
                </p>
            </div>

            <div class="border-t pt-4">
                <label class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" 
                           <?php echo e(old('is_active', $exam->is_active) ? 'checked' : ''); ?>

                           class="mr-2 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded">
                    <span class="font-medium">Exam is Active (visible to students)</span>
                </label>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                    Update Exam
                </button>
                <a href="<?php echo e(route('admin.exams')); ?>" 
                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-8 py-3 rounded-lg font-semibold">
                    Cancel
                </a>
            </div>
        </form>

        <div class="mt-8 pt-6 border-t">
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Quick Actions</h3>
            <div class="flex gap-3">
                <a href="<?php echo e(route('admin.exam.questions', $exam->id)); ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                    Manage Questions (<?php echo e($exam->questions->count()); ?>)
                </a>
                <a href="<?php echo e(route('admin.exam.results', $exam->id)); ?>" 
                   class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded">
                    View Results
                </a>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\CBT PROJECT\nigerian-cbt-system\nigerian-cbt-system\resources\views/admin/exams/edit.blade.php ENDPATH**/ ?>