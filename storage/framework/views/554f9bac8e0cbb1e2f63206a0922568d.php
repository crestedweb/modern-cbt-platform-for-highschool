

<?php $__env->startSection('title', 'Take Exam'); ?>

<?php $__env->startSection('content'); ?>
<div x-data="examApp()" x-init="init()" class="space-y-6">
    <!-- Timer and Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800"><?php echo e($attempt->exam->title); ?></h2>
                <p class="text-gray-600"><?php echo e($attempt->exam->subject); ?></p>
            </div>
            <div class="text-center">
                <div class="text-3xl font-bold" :class="timeRemaining < 300 ? 'text-red-600' : 'text-green-600'">
                    <span x-text="formatTime(timeRemaining)"></span>
                </div>
                <div class="text-sm text-gray-600">Time Remaining</div>
            </div>
        </div>
    </div>

    <!-- Questions -->
    <form id="exam-form" @submit.prevent="submitExam">
        <?php echo csrf_field(); ?>
        <div class="bg-white rounded-lg shadow p-6 space-y-8">
            <?php $__currentLoopData = $questions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $question): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="border-b pb-6 last:border-b-0" id="question-<?php echo e($question->id); ?>">
                <div class="flex items-start mb-4">
                    <span class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-semibold mr-3 flex-shrink-0">
                        <?php echo e($index + 1); ?>

                    </span>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium mb-2"><?php echo e($question->question_text); ?></p>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                            <?php echo e($question->marks); ?> <?php echo e($question->marks == 1 ? 'mark' : 'marks'); ?>

                        </span>
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded ml-2">
                            <?php echo e(ucwords(str_replace('_', ' ', $question->question_type))); ?>

                        </span>
                    </div>
                </div>

                <?php
                    $savedAnswer = $attempt->answers->where('question_id', $question->id)->first();
                ?>

                <div class="ml-11">
                    <?php if($question->question_type === 'multiple_choice'): ?>
                        <!-- Multiple Choice -->
                        <div class="space-y-2">
                            <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer transition">
                                <input 
                                    type="radio" 
                                    name="question_<?php echo e($question->id); ?>" 
                                    value="<?php echo e($key); ?>"
                                    <?php echo e($savedAnswer && $savedAnswer->answer_text == $key ? 'checked' : ''); ?>

                                    @change="saveAnswer(<?php echo e($question->id); ?>, $event.target.value)"
                                    class="mr-3 h-4 w-4 text-green-600"
                                >
                                <span><strong><?php echo e($key); ?>.</strong> <?php echo e($option); ?></span>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                    <?php elseif($question->question_type === 'fill_blank'): ?>
                        <!-- Fill in the Blank -->
                        <input 
                            type="text" 
                            name="question_<?php echo e($question->id); ?>"
                            value="<?php echo e($savedAnswer->answer_text ?? ''); ?>"
                            @change="saveAnswer(<?php echo e($question->id); ?>, $event.target.value)"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Type your answer here..."
                        >

                    <?php elseif($question->question_type === 'theory'): ?>
                        <!-- Theory/Essay -->
                        <textarea 
                            name="question_<?php echo e($question->id); ?>"
                            rows="8"
                            @change="saveAnswer(<?php echo e($question->id); ?>, $event.target.value)"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Write your answer here..."
                        ><?php echo e($savedAnswer->answer_text ?? ''); ?></textarea>

                    <?php elseif($question->question_type === 'coding'): ?>
                        <!-- Coding -->
                        <div>
                            <div class="bg-gray-800 text-gray-200 p-2 rounded-t text-xs font-mono">
                                Code Editor - Write your code below
                            </div>
                            <textarea 
                                name="question_<?php echo e($question->id); ?>"
                                rows="12"
                                @change="saveAnswer(<?php echo e($question->id); ?>, $event.target.value)"
                                class="w-full px-4 py-2 border border-t-0 rounded-b-lg font-mono text-sm focus:ring-2 focus:ring-green-500 bg-gray-50"
                                placeholder="// Write your code here..."
                            ><?php echo e($savedAnswer->answer_text ?? ''); ?></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <!-- Submit Section -->
        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span x-show="isSaving" class="text-yellow-600">💾 Saving...</span>
                    <span x-show="lastSaved && !isSaving" class="text-green-600">✓ All answers saved</span>
                    <span class="ml-4">Auto-save every 30 seconds</span>
                </div>
                <button 
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold transition"
                    :disabled="isSubmitting"
                    :class="{ 'opacity-50 cursor-not-allowed': isSubmitting }"
                >
                    <span x-show="!isSubmitting">Submit Exam</span>
                    <span x-show="isSubmitting">Submitting...</span>
                </button>
            </div>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
function examApp() {
    return {
        timeRemaining: <?php echo e($attempt->time_remaining ?? ($attempt->exam->duration_minutes * 60)); ?>,
        timer: null,
        isSaving: false,
        isSubmitting: false,
        lastSaved: false,
        timeExpired: false,

        init() {
            // Check if time has already expired
            if (this.timeRemaining <= 0) {
                this.timeExpired = true;
                this.submitExam(true); // Auto-submit without confirmation
            } else {
                this.startTimer();
                this.autoSave();
            }
        },

        startTimer() {
            this.timer = setInterval(() => {
                if (this.timeRemaining > 0) {
                    this.timeRemaining--;
                } else {
                    if (!this.timeExpired) {
                        this.timeExpired = true;
                        clearInterval(this.timer);
                        this.submitExam(true); // Pass true to skip confirmation
                    }
                }
            }, 1000);
        },

        formatTime(seconds) {
            if (seconds < 0) seconds = 0;
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
        },

        autoSave() {
            setInterval(() => {
                if (!this.timeExpired && this.timeRemaining > 0) {
                    this.saveCurrentAnswers();
                }
            }, 30000); // Auto-save every 30 seconds
        },

        async saveAnswer(questionId, answer) {
            if (this.timeExpired || this.isSubmitting) return;

            this.isSaving = true;
            this.lastSaved = false;

            try {
                const response = await fetch('<?php echo e(route("student.save-answer", $attempt->id)); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        question_id: questionId,
                        answer_text: answer,
                        time_remaining: this.timeRemaining
                    })
                });

                if (response.ok) {
                    this.lastSaved = true;
                    setTimeout(() => this.lastSaved = false, 2000);
                }
            } catch (error) {
                console.error('Save error:', error);
            } finally {
                this.isSaving = false;
            }
        },

        async saveCurrentAnswers() {
            if (this.timeExpired || this.isSubmitting) return;

            const form = document.getElementById('exam-form');
            const formData = new FormData(form);
            
            for (let [key, value] of formData.entries()) {
                if (key.startsWith('question_')) {
                    const questionId = key.replace('question_', '');
                    await this.saveAnswer(questionId, value);
                }
            }
        },

        async submitExam(autoSubmit = false) {
            // Prevent double submission
            if (this.isSubmitting) return;

            // Show confirmation unless auto-submitting
            if (!autoSubmit && !confirm('Are you sure you want to submit this exam? You cannot change your answers after submission.')) {
                return;
            }

            // If time expired, show alert once
            if (autoSubmit && this.timeRemaining <= 0) {
                alert('Time is up! Submitting your exam automatically.');
            }

            clearInterval(this.timer);
            this.isSubmitting = true;
            this.timeExpired = true;

            // Save all current answers first
            await this.saveCurrentAnswers();

            // Submit the exam
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo e(route("student.submit-exam", $attempt->id)); ?>';
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            form.appendChild(csrf);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\CBT PROJECT\CBT PROJECT\nigerian-cbt-system\nigerian-cbt-system\resources\views/student/take-exam.blade.php ENDPATH**/ ?>