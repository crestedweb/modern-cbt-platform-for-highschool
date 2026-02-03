

<?php $__env->startSection('title', 'Manage Questions'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?php echo e($exam->title); ?></h2>
                <p class="text-gray-600"><?php echo e($exam->subject); ?> - <?php echo e($exam->duration_minutes); ?> minutes</p>
            </div>
            <a href="<?php echo e(route('admin.exams')); ?>" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                Back to Exams
            </a>
        </div>
        <div class="grid grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Total Questions:</span>
                <p class="font-semibold text-lg"><?php echo e($exam->questions->count()); ?></p>
            </div>
            <div>
                <span class="text-gray-600">Total Marks:</span>
                <p class="font-semibold text-lg"><?php echo e($exam->questions->sum('marks')); ?> / <?php echo e($exam->total_marks); ?></p>
            </div>
            <div>
                <span class="text-gray-600">Status:</span>
                <p class="font-semibold text-lg">
                    <?php if($exam->questions->sum('marks') == $exam->total_marks): ?>
                    <span class="text-green-600">✓ Complete</span>
                    <?php else: ?>
                    <span class="text-yellow-600">⚠ Incomplete</span>
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Add Question Form -->
    <div class="bg-white rounded-lg shadow p-6" x-data="{ questionType: 'multiple_choice' }">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Add New Question</h3>

        <?php if($errors->any()): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
        <?php endif; ?>

       <form action="<?php echo e(route('admin.exam.question.store', $exam->id)); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
    <?php echo csrf_field(); ?>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Question Type *</label>
        <select name="question_type" x-model="questionType"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
            <option value="multiple_choice">Multiple Choice (A/B/C/D)</option>
            <option value="theory">Theory/Essay</option>
            <option value="coding">Coding</option>
            <option value="fill_blank">Fill in the Blank</option>
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Question Text *</label>
        <textarea name="question_text" rows="3" required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500"
                  placeholder="Enter your question here"><?php echo e(old('question_text')); ?></textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Marks *</label>
        <input type="number" name="marks" value="<?php echo e(old('marks', 1)); ?>" min="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
    </div>
    <!-- Image Upload (for all question types, especially coding/theory) -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Reference Image <span class="text-gray-500">(Optional)</span>
    </label>
    <div x-data="{ preview: null }" class="space-y-2">
        <input type="file" 
               name="image" 
               accept="image/*"
               @change="
                   const file = $event.target.files[0];
                   if (file) {
                       const reader = new FileReader();
                       reader.onload = (e) => { preview = e.target.result };
                       reader.readAsDataURL(file);
                   }
               "
               class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-500">
        <p class="text-xs text-gray-500">Upload an image showing what students should design or reference. Max 5MB (JPG, PNG, GIF, WEBP)</p>

        <!-- Image Preview -->
        <div x-show="preview" class="mt-3 border rounded-lg overflow-hidden">
            <div class="bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 flex justify-between items-center">
                <span>Preview</span>
                <button type="button" 
                        @click="preview = null; $event.target.closest('div').querySelector('input[type=file]').value = ''"
                        class="text-red-500 hover:text-red-700">Remove</button>
            </div>
            <img :src="preview" alt="Preview" class="max-h-48 w-full object-contain bg-white p-2">
        </div>
    </div>
</div>

    <!-- Multiple Choice Options -->
    <div x-show="questionType === 'multiple_choice'" class="space-y-3">
        <label class="block text-sm font-medium text-gray-700">Options *</label>
        
        <div class="space-y-2">
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">A.</span>
                <input type="text" name="options[A]" value="<?php echo e(old('options.A')); ?>"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option A">
            </div>
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">B.</span>
                <input type="text" name="options[B]" value="<?php echo e(old('options.B')); ?>"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option B">
            </div>
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">C.</span>
                <input type="text" name="options[C]" value="<?php echo e(old('options.C')); ?>"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option C">
            </div>
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">D.</span>
                <input type="text" name="options[D]" value="<?php echo e(old('options.D')); ?>"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option D">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer *</label>
            <select name="correct_answer"
                    :required="questionType === 'multiple_choice'"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <option value="">-- Select Correct Answer --</option>
                <option value="A" <?php echo e(old('correct_answer') == 'A' ? 'selected' : ''); ?>>A</option>
                <option value="B" <?php echo e(old('correct_answer') == 'B' ? 'selected' : ''); ?>>B</option>
                <option value="C" <?php echo e(old('correct_answer') == 'C' ? 'selected' : ''); ?>>C</option>
                <option value="D" <?php echo e(old('correct_answer') == 'D' ? 'selected' : ''); ?>>D</option>
            </select>
        </div>
    </div>

    <!-- Fill in the Blank Answer -->
    <template x-if="questionType === 'fill_blank'">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer *</label>
            <input type="text" name="correct_answer" value="<?php echo e(old('correct_answer')); ?>"
                   required
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg"
                   placeholder="Enter the correct answer (case-insensitive)">
        </div>
    </template>

    <button type="submit" 
            class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
        Add Question
    </button>
</form>
    </div>

    <!-- Questions List -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-800">Questions (<?php echo e($exam->questions->count()); ?>)</h3>
        </div>

        <?php $__empty_1 = true; $__currentLoopData = $exam->questions->sortBy('order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="border-b p-6">
            <div class="flex justify-between items-start mb-3">
                <div class="flex items-start flex-1">
                    <span class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-semibold mr-3 flex-shrink-0">
                        <?php echo e($index + 1); ?>

                    </span>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium mb-2"><?php echo e($question->question_text); ?></p>
                        <div class="flex gap-2 text-xs">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                <?php echo e(ucwords(str_replace('_', ' ', $question->question_type))); ?>

                            </span>
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">
                                <?php echo e($question->marks); ?> <?php echo e($question->marks == 1 ? 'mark' : 'marks'); ?>

                            </span>
                        </div>

                        <?php if($question->question_type === 'multiple_choice' && $question->options): ?>
                        <div class="mt-3 space-y-1">
                            <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <p class="text-sm <?php echo e($key === $question->correct_answer ? 'text-green-600 font-semibold' : 'text-gray-600'); ?>">
                                <?php echo e($key); ?>. <?php echo e($option); ?>

                                <?php if($key === $question->correct_answer): ?>
                                <span class="text-xs">(Correct Answer)</span>
                                <?php endif; ?>
                            </p>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                        <?php endif; ?>

                        <?php if($question->question_type === 'fill_blank'): ?>
                        <p class="text-sm text-green-600 font-semibold mt-2">
                            Answer: <?php echo e($question->correct_answer); ?>

                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <form action="<?php echo e(route('admin.question.delete', $question->id)); ?>" method="POST" 
                      onsubmit="return confirm('Delete this question?')">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="text-red-600 hover:text-red-800 ml-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="p-12 text-center">
            <div class="text-gray-400 text-6xl mb-4">❓</div>
            <p class="text-gray-600 text-lg">No questions added yet</p>
            <p class="text-gray-500 text-sm">Use the form above to add your first question</p>
        </div>
        <?php endif; ?>
    </div>

    <?php if($exam->questions->count() > 0): ?>
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex justify-between items-center">
            <div>
                <h4 class="font-semibold text-green-800">Exam Ready!</h4>
                <p class="text-sm text-green-600">You have <?php echo e($exam->questions->count()); ?> questions totaling <?php echo e($exam->questions->sum('marks')); ?> marks</p>
            </div>
            <div class="flex gap-3">
                <a href="<?php echo e(route('admin.exam.results', $exam->id)); ?>" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                    View Results
                </a>
                <a href="<?php echo e(route('admin.exams')); ?>" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                    Done
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\CBT PROJECT\CBT PROJECT\nigerian-cbt-system\nigerian-cbt-system\resources\views/admin/exams/questions.blade.php ENDPATH**/ ?>