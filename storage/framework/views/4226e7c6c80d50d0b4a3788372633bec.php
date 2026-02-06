

<?php $__env->startSection('title', 'Manage Students'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-2xl font-bold text-gray-800">Manage Students by Year</h2>
        <a href="<?php echo e(route('admin.student.create')); ?>" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
            + Add New Student
        </a>
    </div>

    <!-- Statistics Overview -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Total Students</div>
            <div class="text-3xl font-bold text-blue-600"><?php echo e($students->count()); ?></div>
        </div>
        <?php
            $yearGroups = ['Year 7', 'Year 8', 'Year 9'];
            $seniorYears = ['Year 10', 'Year 11', 'Year 12'];
        ?>
        <div class="bg-green-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Junior (Y7-Y9)</div>
            <div class="text-3xl font-bold text-green-600">
                <?php echo e($students->whereIn('class.name', $yearGroups)->count()); ?>

            </div>
        </div>
        <div class="bg-purple-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Senior (Y10-Y12)</div>
            <div class="text-3xl font-bold text-purple-600">
                <?php echo e($students->whereIn('class.name', $seniorYears)->count()); ?>

            </div>
        </div>
        <div class="bg-orange-50 p-4 rounded-lg">
            <div class="text-sm text-gray-600">Unassigned</div>
            <div class="text-3xl font-bold text-orange-600">
                <?php echo e($students->whereNull('class_id')->count()); ?>

            </div>
        </div>
    </div>

    <!-- Junior Secondary Section -->
    <div>
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <span class="bg-green-600 text-white px-3 py-1 rounded-lg mr-2">Junior Secondary</span>
            Year 7 - Year 9
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php $__currentLoopData = ['Year 7', 'Year 8', 'Year 9']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $class = $students->where('class.name', $yearName)->first()->class ?? null;
                $classStudents = $students->where('class.name', $yearName);
            ?>
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4">
                    <h4 class="text-xl font-bold"><?php echo e($yearName); ?></h4>
                    <p class="text-sm opacity-90">
                        <?php if($class): ?>
                            <?php echo e($class->description); ?>

                        <?php else: ?>
                            Grade <?php echo e(str_replace('Year ', '', $yearName)); ?>

                        <?php endif; ?>
                    </p>
                </div>

                <!-- Student Count -->
                <div class="bg-green-50 p-3 border-b">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-700">Total Students:</span>
                        <span class="text-2xl font-bold text-green-600"><?php echo e($classStudents->count()); ?></span>
                    </div>
                </div>

                <!-- Students List -->
                <div class="max-h-96 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $classStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 border-b hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900"><?php echo e($student->name); ?></p>
                                <p class="text-sm text-gray-600"><?php echo e($student->registration_number); ?></p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <a href="<?php echo e(route('admin.student.edit', $student->id)); ?>" 
                                   class="text-blue-600 hover:text-blue-800 text-sm" title="Edit">
                                    ✏️
                                </a>
                                <form action="<?php echo e(route('admin.student.delete', $student->id)); ?>" method="POST" 
                                      onsubmit="return confirm('Delete <?php echo e($student->name); ?>?')" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm" title="Delete">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center text-gray-500">
                        <p class="text-4xl mb-2">📚</p>
                        <p class="text-sm">No students yet</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Card Footer -->
                <?php if($classStudents->count() > 0): ?>
                <div class="bg-gray-50 p-3 text-center">
                    <a href="<?php echo e(route('admin.student.create')); ?>" 
                       class="text-sm text-green-600 hover:text-green-800 font-semibold">
                        + Add Student to <?php echo e($yearName); ?>

                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Senior Secondary Section -->
    <div>
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <span class="bg-purple-600 text-white px-3 py-1 rounded-lg mr-2">Senior Secondary</span>
            Year 10 - Year 12
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php $__currentLoopData = ['Year 10', 'Year 11', 'Year 12']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $yearName): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $class = $students->where('class.name', $yearName)->first()->class ?? null;
                $classStudents = $students->where('class.name', $yearName);
            ?>
            
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <!-- Card Header -->
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4">
                    <h4 class="text-xl font-bold"><?php echo e($yearName); ?></h4>
                    <p class="text-sm opacity-90">
                        <?php if($class): ?>
                            <?php echo e($class->description); ?>

                        <?php else: ?>
                            Grade <?php echo e(str_replace('Year ', '', $yearName)); ?>

                        <?php endif; ?>
                    </p>
                </div>

                <!-- Student Count -->
                <div class="bg-purple-50 p-3 border-b">
                    <div class="flex justify-between items-center">
                        <span class="text-sm font-semibold text-gray-700">Total Students:</span>
                        <span class="text-2xl font-bold text-purple-600"><?php echo e($classStudents->count()); ?></span>
                    </div>
                </div>

                <!-- Students List -->
                <div class="max-h-96 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $classStudents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 border-b hover:bg-gray-50 transition">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900"><?php echo e($student->name); ?></p>
                                <p class="text-sm text-gray-600"><?php echo e($student->registration_number); ?></p>
                            </div>
                            <div class="flex gap-2 ml-2">
                                <a href="<?php echo e(route('admin.student.edit', $student->id)); ?>" 
                                   class="text-blue-600 hover:text-blue-800 text-sm" title="Edit">
                                    ✏️
                                </a>
                                <form action="<?php echo e(route('admin.student.delete', $student->id)); ?>" method="POST" 
                                      onsubmit="return confirm('Delete <?php echo e($student->name); ?>?')" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm" title="Delete">
                                        🗑️
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center text-gray-500">
                        <p class="text-4xl mb-2">📚</p>
                        <p class="text-sm">No students yet</p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Card Footer -->
                <?php if($classStudents->count() > 0): ?>
                <div class="bg-gray-50 p-3 text-center">
                    <a href="<?php echo e(route('admin.student.create')); ?>" 
                       class="text-sm text-purple-600 hover:text-purple-800 font-semibold">
                        + Add Student to <?php echo e($yearName); ?>

                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <!-- Unassigned Students -->
    <?php if($students->whereNull('class_id')->count() > 0): ?>
    <div>
        <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center">
            <span class="bg-orange-600 text-white px-3 py-1 rounded-lg mr-2">⚠️ Unassigned</span>
            Students Without Class
        </h3>
        
        <div class="bg-white rounded-lg shadow p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php $__currentLoopData = $students->whereNull('class_id'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border border-orange-300 bg-orange-50 rounded-lg p-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-900"><?php echo e($student->name); ?></p>
                            <p class="text-sm text-gray-600"><?php echo e($student->registration_number); ?></p>
                        </div>
                        <div class="flex gap-2">
                            <a href="<?php echo e(route('admin.student.edit', $student->id)); ?>" 
                               class="text-blue-600 hover:text-blue-800" title="Assign Class">
                                ✏️
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cbt\resources\views/admin/students/index.blade.php ENDPATH**/ ?>