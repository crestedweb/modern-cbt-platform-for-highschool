

<?php $__env->startSection('title', 'Manage Classes'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Manage Classes</h2>
    </div>

    <!-- Add New Class Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Add New Class</h3>
        
        <form action="<?php echo e(route('admin.class.store')); ?>" method="POST" class="flex gap-4 items-end">
            <?php echo csrf_field(); ?>
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Class Name *</label>
                <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="e.g., SS1, JSS2, Year 10">
                <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <input type="text" name="description" value="<?php echo e(old('description')); ?>"
                       class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                       placeholder="e.g., Senior Secondary 1">
                <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-sm mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">
                Add Class
            </button>
        </form>
    </div>

    <!-- Classes List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Class Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Students</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exams</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php $__empty_1 = true; $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm"><?php echo e($index + 1); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="font-medium text-gray-900"><?php echo e($class->name); ?></div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo e($class->description); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo e($class->students_count); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600"><?php echo e($class->exams_count); ?></td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <form action="<?php echo e(route('admin.class.delete', $class->id)); ?>" method="POST" 
                              onsubmit="return confirm('Delete this class? Students will be unassigned.')">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-red-600 hover:text-red-800">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        No classes yet. Add one above to get started.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\USER\Desktop\CBT PROJECT\nigerian-cbt-system\nigerian-cbt-system\resources\views/admin/classes/index.blade.php ENDPATH**/ ?>