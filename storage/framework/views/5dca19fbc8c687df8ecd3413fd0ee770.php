<?php $__env->startSection('title', 'Student Dashboard'); ?>
<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome, <?php echo e(auth()->user()->name); ?>!</h2>
        <p class="text-gray-600">Registration Number: <?php echo e(auth()->user()->registration_number); ?></p>
        <p class="text-gray-600">Class: <?php echo e(auth()->user()->class->name ?? 'N/A'); ?></p>
    </div>

    <?php if($inProgressAttempts->count() > 0): ?>
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <h3 class="font-semibold text-yellow-800 mb-2">⚠️ Exams In Progress</h3>
        <?php $__currentLoopData = $inProgressAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="flex justify-between items-center bg-white p-3 rounded mb-2">
            <div>
                <p class="font-medium"><?php echo e($attempt->exam->title); ?></p>
                <p class="text-sm text-gray-600">Started: <?php echo e($attempt->started_at->format('d M Y, h:i A')); ?></p>
            </div>
            <a href="<?php echo e(route('student.take-exam', $attempt->id)); ?>" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">Continue Exam</a>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-800">📝 Available Exams</h3>
        </div>
        <div class="p-6">
            <?php $__empty_1 = true; $__currentLoopData = $availableExams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border rounded-lg p-4 mb-4 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-800"><?php echo e($exam->title); ?></h4>
                        <p class="text-gray-600 text-sm mt-1"><?php echo e($exam->description); ?></p>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full"><?php echo e($exam->subject); ?></span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="text-sm">
                        <span class="text-gray-600">Duration:</span>
                        <p class="font-semibold"><?php echo e($exam->duration_minutes); ?> mins</p>
                    </div>
                    <div class="text-sm">
                        <span class="text-gray-600">Total Marks:</span>
                        <p class="font-semibold"><?php echo e($exam->total_marks); ?></p>
                    </div>
                    <div class="text-sm">
                        <span class="text-gray-600">Pass Mark:</span>
                        <p class="font-semibold"><?php echo e($exam->pass_mark); ?></p>
                    </div>
                    <div class="text-sm">
                        <span class="text-gray-600">Questions:</span>
                        <p class="font-semibold"><?php echo e($exam->questions->count()); ?></p>
                    </div>
                </div>
                <?php if($exam->instructions): ?>
                <div class="bg-blue-50 p-3 rounded mb-3">
                    <p class="text-sm text-gray-700"><strong>Instructions:</strong> <?php echo e($exam->instructions); ?></p>
                </div>
                <?php endif; ?>
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">Available until: <?php echo e($exam->end_date->format('d M Y')); ?></div>
                    <a href="<?php echo e(route('student.start-exam', $exam->id)); ?>" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">Start Exam</a>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-600 text-center py-8">No exams available at the moment.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-800">📊 Exam History</h3>
        </div>
        <div class="p-6">
            <?php $__empty_1 = true; $__currentLoopData = $completedAttempts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attempt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="border rounded-lg p-4 mb-3 flex justify-between items-center">
                <div>
                    <h4 class="font-semibold text-gray-800"><?php echo e($attempt->exam->title); ?></h4>
                    <p class="text-sm text-gray-600">Submitted: <?php echo e($attempt->submitted_at->format('d M Y, h:i A')); ?></p>
                    <?php if($attempt->isGraded()): ?>
                    <p class="text-sm mt-1">
                        <span class="font-semibold">Score:</span> 
                        <span class="text-lg font-bold <?php echo e($attempt->total_score >= $attempt->exam->pass_mark ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php echo e($attempt->total_score); ?>/<?php echo e($attempt->exam->total_marks); ?>

                        </span>
                    </p>
                    <?php else: ?>
                    <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded mt-1">Pending Grading</span>
                    <?php endif; ?>
                </div>
                <a href="<?php echo e(route('student.view-result', $attempt->id)); ?>" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">View Details</a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="text-gray-600 text-center py-8">No completed exams yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cbt\resources\views/student/dashboard.blade.php ENDPATH**/ ?>