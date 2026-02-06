

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
                    <!-- Reference Image (if uploaded by teacher) -->
                    <?php if($question->image_path): ?>
                    <div class="mb-6 border rounded-lg overflow-hidden">
                        <div class="bg-blue-50 px-4 py-2 border-b flex justify-between items-center">
                            <span class="text-sm font-semibold text-blue-700">Target Design / Reference Image</span>
                            <button type="button"
                                    x-data="{ zoomed: false }"
                                    @click="zoomed = !zoomed"
                                    class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded">
                                <span x-show="!zoomed">🔍 Zoom In</span>
                                <span x-show="zoomed">🔙 Zoom Out</span>
                            </button>
                        </div>

                        <!-- Normal View -->
                        <div x-show="!zoomed" class="p-4 bg-white flex justify-center">
                            <img src="<?php echo e($question->getImageUrl()); ?>" 
                                 alt="Reference design" 
                                 class="max-h-64 object-contain border border-gray-200 rounded shadow-sm">
                        </div>

                        <!-- Zoomed View (Modal) -->
                        <div x-show="zoomed" 
                             class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4"
                             @click="zoomed = false">
                            <div class="relative max-w-4xl max-h-full" @click.stop>
                                <button @click="zoomed = false"
                                        class="absolute top-2 right-2 bg-white text-gray-800 rounded-full w-8 h-8 flex items-center justify-center shadow-lg hover:bg-gray-100">
                                    ✕
                                </button>
                                <img src="<?php echo e($question->getImageUrl()); ?>" 
                                     alt="Reference design zoomed" 
                                     class="max-w-full max-h-screen object-contain rounded-lg shadow-xl">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

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
    <!-- Coding with Multi-File Support -->
    <div x-data="{ showPreview: false }" data-question="<?php echo e($question->id); ?>">
        <!-- File Tabs -->
        <div class="bg-gray-900 p-2 rounded-t flex gap-2 items-center flex-wrap">
            <button type="button" 
                    onclick="switchFile(<?php echo e($question->id); ?>, 'index.html')"
                    class="file-tab bg-green-600 text-white px-3 py-1 rounded text-xs font-semibold">
                📄 index.html
            </button>
            <button type="button"
                    onclick="switchFile(<?php echo e($question->id); ?>, 'styles.css')"
                    class="file-tab bg-gray-700 text-gray-300 px-3 py-1 rounded text-xs font-semibold">
                🎨 styles.css
            </button>
            <button type="button"
                    onclick="switchFile(<?php echo e($question->id); ?>, 'script.js')"
                    class="file-tab bg-gray-700 text-gray-300 px-3 py-1 rounded text-xs font-semibold">
                ⚡ script.js
            </button>
            
            <div class="ml-auto flex gap-2">
                <select id="template-select-<?php echo e($question->id); ?>" 
                        onchange="loadTemplate(<?php echo e($question->id); ?>, this.value)"
                        class="bg-gray-700 text-white text-xs px-2 py-1 rounded">
                    <option value="">Load Template...</option>
                    <option value="html-basic">HTML Basic</option>
                    <option value="html-form">HTML Form</option>
                    <option value="css-card">CSS Card</option>
                </select>
                
                <button type="button" 
                        @click="showPreview = !showPreview"
                        class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1 rounded font-semibold">
                    <span x-show="!showPreview">👁️ Preview</span>
                    <span x-show="showPreview">📝 Code</span>
                </button>
            </div>
        </div>

        <!-- Editor + Preview -->
        <div class="grid" :class="showPreview ? 'grid-cols-2 gap-2' : 'grid-cols-1'">
            <div>
                <textarea 
                    id="code-editor-<?php echo e($question->id); ?>"
                    name="question_<?php echo e($question->id); ?>"
                    class="code-editor"
                    data-question-id="<?php echo e($question->id); ?>"
                ><?php echo e($savedAnswer->answer_text ?? ''); ?></textarea>
            </div>

            <div x-show="showPreview" class="border rounded overflow-hidden bg-white">
                <div class="bg-gray-100 p-2 border-b flex justify-between items-center">
                    <span class="text-xs font-semibold">Live Preview</span>
                    <button type="button" 
                            @click="updatePreview(<?php echo e($question->id); ?>)"
                            class="text-xs bg-blue-500 text-white px-2 py-1 rounded">
                        🔄 Refresh
                    </button>
                </div>
                <iframe 
                    id="preview-frame-<?php echo e($question->id); ?>"
                    class="w-full bg-white"
                    style="height: 350px; border: none;"
                    sandbox="allow-scripts"
                ></iframe>
            </div>
        </div>
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
<!-- CodeMirror CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/monokai.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.css">

<!-- CodeMirror JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>

<!-- Autocomplete -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/html-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/css-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/javascript-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/xml-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closetag.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchtags.min.js"></script>

<script>
let editors = {};
let projectFiles = {}; // Store multiple files per question

document.addEventListener('DOMContentLoaded', function() {
    const codeEditors = document.querySelectorAll('.code-editor');
    
    codeEditors.forEach(function(textarea) {
        const questionId = textarea.dataset.questionId;
        
        // Initialize project files for this question
        if (!projectFiles[questionId]) {
            projectFiles[questionId] = {
                'index.html': textarea.value || '<!DOCTYPE html>\n<html>\n<head>\n    <title>My Project</title>\n    <link rel="stylesheet" href="styles.css">\n</head>\n<body>\n    <h1>Hello World!</h1>\n    <script src="script.js"></script>\n</body>\n</html>',
                'styles.css': '/* CSS styles */\nbody {\n    font-family: Arial, sans-serif;\n    margin: 20px;\n}\n',
                'script.js': '// JavaScript code\nconsole.log("Hello World!");'
            };
        }
        
        const editor = CodeMirror.fromTextArea(textarea, {
            mode: 'htmlmixed',
            theme: 'monokai',
            lineNumbers: true,
            autoCloseBrackets: true,
            autoCloseTags: true,
            matchBrackets: true,
            matchTags: true,
            indentUnit: 4,
            lineWrapping: true,
            extraKeys: {
                "Ctrl-Space": "autocomplete",
                "'<'": function(cm) {
                    cm.replaceSelection("<");
                    setTimeout(function() {
                        CodeMirror.commands.autocomplete(cm);
                    }, 100);
                },
                "Tab": function(cm) {
                    if (cm.somethingSelected()) {
                        cm.indentSelection("add");
                    } else {
                        cm.replaceSelection("    ", "end");
                    }
                }
            },
            hintOptions: {
                completeSingle: false
            }
        });
        
        editors[questionId] = {
            editor: editor,
            currentFile: 'index.html'
        };
        
        // Load initial file
        editor.setValue(projectFiles[questionId]['index.html']);
        
        // Auto-save and preview on change
        let updateTimeout;
        editor.on('change', function() {
            const code = editor.getValue();
            textarea.value = code;
            
            // Save to current file
            projectFiles[questionId][editors[questionId].currentFile] = code;
            
            clearTimeout(updateTimeout);
            updateTimeout = setTimeout(function() {
                updatePreview(questionId);
            }, 1000);
        });
        
        // Autocomplete on input
        editor.on('inputRead', function(cm, change) {
            if (!cm.state.completionActive) {
                const char = change.text[0];
                if (char && char.match(/[a-zA-Z<]/)) {
                    CodeMirror.commands.autocomplete(cm, null, {completeSingle: false});
                }
            }
        });
        
        // Language switcher
        const langSelect = document.getElementById('language-select-' + questionId);
        if (langSelect) {
            langSelect.addEventListener('change', function() {
                const modes = {
                    'html': 'htmlmixed',
                    'css': 'css',
                    'javascript': 'javascript'
                };
                editor.setOption('mode', modes[this.value] || 'htmlmixed');
            });
        }
        
        // Initial preview
        setTimeout(() => updatePreview(questionId), 500);
    });
});

function updatePreview(questionId) {
    const frame = document.getElementById('preview-frame-' + questionId);
    if (!frame) return;
    
    const files = projectFiles[questionId];
    let html = files['index.html'] || '';
    
    // Inject CSS
    if (files['styles.css']) {
        html = html.replace('</head>', '<style>' + files['styles.css'] + '</style></head>');
    }
    
    // Inject JS
    if (files['script.js']) {
        html = html.replace('</body>', '<script>' + files['script.js'] + '</script></body>');
    }
    
    const doc = frame.contentDocument || frame.contentWindow.document;
    doc.open();
    doc.write(html);
    doc.close();
}

function switchFile(questionId, filename) {
    const editorObj = editors[questionId];
    if (!editorObj) return;
    
    // Save current file
    projectFiles[questionId][editorObj.currentFile] = editorObj.editor.getValue();
    
    // Switch to new file
    editorObj.currentFile = filename;
    editorObj.editor.setValue(projectFiles[questionId][filename] || '');
    
    // Update mode
    const modes = {
        'index.html': 'htmlmixed',
        'styles.css': 'css',
        'script.js': 'javascript'
    };
    editorObj.editor.setOption('mode', modes[filename] || 'htmlmixed');
    
    // Update active button
    document.querySelectorAll(`[data-question="${questionId}"] .file-tab`).forEach(btn => {
        btn.classList.remove('bg-green-600', 'text-white');
        btn.classList.add('bg-gray-700', 'text-gray-300');
    });
    event.target.classList.remove('bg-gray-700', 'text-gray-300');
    event.target.classList.add('bg-green-600', 'text-white');
}

function loadTemplate(questionId, templateKey) {
    const templates = {
        'html-basic': {
            'index.html': '<!DOCTYPE html>\n<html>\n<head>\n    <title>My Page</title>\n    <link rel="stylesheet" href="styles.css">\n</head>\n<body>\n    <h1>Hello World!</h1>\n    <p>This is a paragraph.</p>\n</body>\n</html>',
            'styles.css': 'body {\n    font-family: Arial;\n    margin: 20px;\n    background: #f0f0f0;\n}\n\nh1 {\n    color: #333;\n}',
            'script.js': 'console.log("Page loaded!");'
        },
        'html-form': {
            'index.html': '<!DOCTYPE html>\n<html>\n<head>\n    <title>Form</title>\n    <link rel="stylesheet" href="styles.css">\n</head>\n<body>\n    <form>\n        <label>Name:</label>\n        <input type="text" id="name">\n        <button type="button" onclick="greet()">Submit</button>\n    </form>\n    <script src="script.js"></script>\n</body>\n</html>',
            'styles.css': 'form {\n    max-width: 400px;\n    margin: 40px auto;\n    padding: 20px;\n    background: white;\n    border-radius: 8px;\n}\n\ninput {\n    display: block;\n    width: 100%;\n    padding: 10px;\n    margin: 10px 0;\n}',
            'script.js': 'function greet() {\n    const name = document.getElementById("name").value;\n    alert("Hello " + name + "!");\n}'
        },
        'css-card': {
            'index.html': '<!DOCTYPE html>\n<html>\n<head>\n    <link rel="stylesheet" href="styles.css">\n</head>\n<body>\n    <div class="card">\n        <h2>Card Title</h2>\n        <p>Card content here</p>\n    </div>\n</body>\n</html>',
            'styles.css': 'body {\n    background: #f3f4f6;\n    padding: 40px;\n}\n\n.card {\n    background: white;\n    padding: 20px;\n    border-radius: 12px;\n    box-shadow: 0 4px 6px rgba(0,0,0,0.1);\n    max-width: 300px;\n}\n\n.card:hover {\n    transform: translateY(-5px);\n    transition: 0.3s;\n}',
            'script.js': '// Add interactivity here'
        }
    };
    
    if (templates[templateKey]) {
        if (confirm('Load template? This will replace your current files.')) {
            projectFiles[questionId] = templates[templateKey];
            editors[questionId].editor.setValue(projectFiles[questionId]['index.html']);
            updatePreview(questionId);
        }
    }
    
    document.getElementById('template-select-' + questionId).value = '';
}

function examApp() {
    return {
        timeRemaining: <?php echo e($attempt->time_remaining ?? ($attempt->exam->duration_minutes * 60)); ?>,
        timer: null,
        isSaving: false,
        isSubmitting: false,
        lastSaved: false,
        timeExpired: false,

        init() {
            if (this.timeRemaining <= 0) {
                this.timeExpired = true;
                this.submitExam(true);
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
                        this.submitExam(true);
                    }
                }
            }, 1000);
        },

        formatTime(seconds) {
            if (seconds < 0) seconds = 0;
            const hours = Math.floor(seconds / 3600);
            const minutes = Math.floor((seconds % 3600) / 60);
            const secs = seconds % 60;
            return String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        },

        autoSave() {
            setInterval(() => {
                if (!this.timeExpired && this.timeRemaining > 0) {
                    this.saveCurrentAnswers();
                }
            }, 30000);
        },

        async saveAnswer(questionId, answer) {
            if (this.timeExpired || this.isSubmitting) return;
            this.isSaving = true;

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
            if (this.isSubmitting) return;

            if (!autoSubmit && !confirm('Submit exam? You cannot change answers after submission.')) {
                return;
            }

            if (autoSubmit && this.timeRemaining <= 0) {
                alert('Time is up! Submitting automatically.');
            }

            clearInterval(this.timer);
            this.isSubmitting = true;
            this.timeExpired = true;

            await this.saveCurrentAnswers();

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

<style>
.CodeMirror {
    height: 350px;
    font-size: 14px;
    border: 1px solid #e5e7eb;
}

.CodeMirror-hints {
    z-index: 1000;
    font-family: monospace;
    font-size: 13px;
}

.CodeMirror-hint {
    padding: 4px 8px;
}

.CodeMirror-hint-active {
    background: #16a34a;
    color: white;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cbt\resources\views/student/take-exam.blade.php ENDPATH**/ ?>