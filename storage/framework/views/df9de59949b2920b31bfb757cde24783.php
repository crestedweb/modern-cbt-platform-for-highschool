

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
                            <?php echo e($question->marks); ?> marks
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
                   <?php if($question->image_path): ?>
<div class="mb-6 border rounded-lg overflow-hidden" x-data="{ zoomed: false }">
    <div class="bg-blue-50 px-4 py-2 border-b">
        <span class="text-sm font-semibold text-blue-700">Reference Image</span>
        <!-- DEBUG INFO -->
        <div class="text-xs mt-2 bg-yellow-100 p-2 rounded">
            <p><strong>Path in DB:</strong> <?php echo e($question->image_path); ?></p>
            <p><strong>Full URL:</strong> <?php echo e(asset('storage/' . $question->image_path)); ?></p>
            <p><strong>File exists:</strong> <?php echo e(file_exists(public_path('storage/' . $question->image_path)) ? 'YES ✓' : 'NO ✗'); ?></p>
        </div>
    </div>
    
    <div class="p-4 bg-white">
        <img src="<?php echo e(asset('storage/' . $question->image_path)); ?>" 
             alt="Reference" 
             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22200%22 height=%22200%22%3E%3Crect fill=%22%23ddd%22 width=%22200%22 height=%22200%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22 fill=%22%23999%22%3EImage not found%3C/text%3E%3C/svg%3E';"
             class="max-h-64 mx-auto border rounded">
    </div>
</div>
<?php endif; ?>

                    <?php if($question->question_type === 'multiple_choice'): ?>
                        <div class="space-y-2">
                            <?php $__currentLoopData = $question->options; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer">
                                <input 
                                    type="radio" 
                                    name="question_<?php echo e($question->id); ?>" 
                                    value="<?php echo e($key); ?>"
                                    <?php echo e($savedAnswer && $savedAnswer->answer_text == $key ? 'checked' : ''); ?>

                                    @change="saveAnswer(<?php echo e($question->id); ?>, $event.target.value)"
                                    class="mr-3 h-4 w-4 text-green-600">
                                <span><strong><?php echo e($key); ?>.</strong> <?php echo e($option); ?></span>
                            </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                    <?php elseif($question->question_type === 'fill_blank'): ?>
                        <input 
                            type="text" 
                            name="question_<?php echo e($question->id); ?>"
                            value="<?php echo e($savedAnswer->answer_text ?? ''); ?>"
                            @change="saveAnswer(<?php echo e($question->id); ?>, $event.target.value)"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Type your answer...">

                    <?php elseif($question->question_type === 'theory'): ?>
                        <textarea 
                            name="question_<?php echo e($question->id); ?>"
                            rows="8"
                            @change="saveAnswer(<?php echo e($question->id); ?>, $event.target.value)"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Write your answer..."><?php echo e($savedAnswer->answer_text ?? ''); ?></textarea>

                    <?php elseif($question->question_type === 'coding'): ?>
                        <div x-data="{ showPreview: false }" data-question="<?php echo e($question->id); ?>">
                            <div class="bg-gray-900 p-2 rounded-t flex gap-2 flex-wrap items-center">
                                <button type="button" 
                                        onclick="switchFile(<?php echo e($question->id); ?>, 'index.html')"
                                        class="file-tab bg-green-600 text-white px-3 py-1 rounded text-xs">
                                    📄 HTML
                                </button>
                                <button type="button"
                                        onclick="switchFile(<?php echo e($question->id); ?>, 'styles.css')"
                                        class="file-tab bg-gray-700 text-gray-300 px-3 py-1 rounded text-xs">
                                    🎨 CSS
                                </button>
                                <button type="button"
                                        onclick="switchFile(<?php echo e($question->id); ?>, 'script.js')"
                                        class="file-tab bg-gray-700 text-gray-300 px-3 py-1 rounded text-xs">
                                    ⚡ JS
                                </button>
                                
                                <div class="ml-auto flex gap-2">
                                    <button type="button" 
                                            @click="showPreview = !showPreview"
                                            class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1 rounded">
                                        <span x-show="!showPreview">👁️ Preview</span>
                                        <span x-show="showPreview">📝 Code</span>
                                    </button>
                                </div>
                            </div>

                            <div class="grid" :class="showPreview ? 'grid-cols-2 gap-2' : 'grid-cols-1'">
                                <div>
                                    <textarea 
                                        id="code-editor-<?php echo e($question->id); ?>"
                                        name="question_<?php echo e($question->id); ?>"
                                        class="code-editor"
                                        data-question-id="<?php echo e($question->id); ?>"><?php echo e($savedAnswer->answer_text ?? ''); ?></textarea>
                                </div>

                                <div x-show="showPreview" class="border rounded overflow-hidden">
                                    <div class="bg-gray-100 p-2 border-b flex justify-between items-center">
                                        <span class="text-xs font-semibold">Preview</span>
                                        <button type="button" 
                                                @click="updatePreview(<?php echo e($question->id); ?>)"
                                                class="text-xs bg-blue-500 text-white px-2 py-1 rounded">
                                            🔄
                                        </button>
                                    </div>
                                    <iframe 
    id="preview-frame-<?php echo e($question->id); ?>"
    style="width:100%; height:350px; border:none; background:white;"
    sandbox="allow-scripts allow-same-origin">
</iframe>

                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="bg-white rounded-lg shadow p-6 mt-6">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    <span x-show="isSaving" class="text-yellow-600">💾 Saving...</span>
                    <span x-show="lastSaved && !isSaving" class="text-green-600">✓ Saved</span>
                </div>
                <button 
                    type="submit"
                    class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold"
                    :disabled="isSubmitting">
                    <span x-show="!isSubmitting">Submit Exam</span>
                    <span x-show="isSubmitting">Submitting...</span>
                </button>
            </div>
        </div>
    </form>
</div>

<?php $__env->startPush('scripts'); ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/html-hint.min.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/monokai.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/html-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/css-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/javascript-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/anyword-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closetag.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/fold/xml-fold.min.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchtags.min.js"></script>

<script>
    CodeMirror.commands.insertTagPair = function(cm, tag) {
    const cursor = cm.getCursor();
    const closeTag = `</${tag}>`;
    cm.replaceSelection(`<${tag}>${closeTag}`);
    cm.setCursor({ line: cursor.line, ch: cursor.ch + tag.length + 2 });
};

let editors = {};
let projectFiles = {};

// Enhanced CSS autocomplete with colors and values
CodeMirror.registerHelper("hint", "css", function(editor) {
    const cur = editor.getCursor();
    const token = editor.getTokenAt(cur);
    const inner = CodeMirror.innerMode(editor.getMode(), token.state);
    
    if (inner.mode.name !== "css") return;

    const propertyHints = [
        "background", "background-color", "background-image", "background-position", "background-size",
        "color", "border", "border-radius", "margin", "padding", "width", "height",
        "display", "flex", "grid", "position", "top", "left", "right", "bottom",
        "font-size", "font-family", "font-weight", "text-align", "line-height"
    ];

    const colorValues = [
        "#000000", "#ffffff", "#ff0000", "#00ff00", "#0000ff", 
        "#ffff00", "#00ffff", "#ff00ff", "#808080", "#c0c0c0",
        "red", "green", "blue", "yellow", "orange", "purple", "pink",
        "black", "white", "gray", "transparent",
        "rgb(255, 0, 0)", "rgba(0, 0, 0, 0.5)", "hsl(0, 100%, 50%)"
    ];

    const displayValues = ["block", "inline", "inline-block", "flex", "grid", "none"];
    const positionValues = ["relative", "absolute", "fixed", "sticky", "static"];

    const line = editor.getLine(cur.line);
    const colonIndex = line.lastIndexOf(':', cur.ch);
    
    // After colon - suggest values
    if (colonIndex > -1 && colonIndex < cur.ch) {
        const property = line.substring(0, colonIndex).trim().split(/\s+/).pop();
        let values = [];
        
        if (property.includes('color') || property === 'background') {
            values = colorValues;
        } else if (property === 'display') {
            values = displayValues;
        } else if (property === 'position') {
            values = positionValues;
        } else if (property.includes('width') || property.includes('height') || property.includes('margin') || property.includes('padding')) {
            values = ["0", "auto", "10px", "20px", "50px", "100px", "100%", "1em", "1rem"];
        } else {
            values = ["auto", "none", "inherit", "initial"];
        }

        return {
            list: values.map(v => v.includes(':') || v.includes('(') ? v : v + ';'),
            from: CodeMirror.Pos(cur.line, colonIndex + 1),
            to: cur
        };
    }
    
    // Before colon - suggest properties
    return {
        list: propertyHints.map(p => p + ': '),
        from: CodeMirror.Pos(cur.line, token.start),
        to: cur
    };
});

document.addEventListener('DOMContentLoaded', function() {
    document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 's') {
        e.preventDefault();
        alert("Use the autosave or submit button — Ctrl+S is disabled!");
    }
});
    document.querySelectorAll('.code-editor').forEach(function(textarea) {
        const qId = textarea.dataset.questionId;
        
        projectFiles[qId] = {
            'index.html': textarea.value || '<!DOCTYPE html>\n<html>\n<head>\n  <title>My Website</title>\n  <link rel="stylesheet" href="styles.css">\n</head>\n<body>\n    <script src="script.js"><\/script>\n</body>\n</html>',
        'styles.css': '',
            'script.js': '// Your JavaScript here\nconsole.log("Ready!");'
        };
        
        const editor = CodeMirror.fromTextArea(textarea, {
            mode: 'htmlmixed',
            theme: 'monokai',
            lineNumbers: true,
            autoCloseBrackets: true,
            autoCloseTags: true,
            matchBrackets: true,
            matchTags: true,
            lineWrapping: true,
            indentUnit: 2,
            tabSize: 2,
            extraKeys: {
                "Ctrl-Space": "autocomplete",
            "'<'": function(cm) {
    if (cm.getMode().name === "htmlmixed") {
        cm.replaceSelection("<");
        setTimeout(() => {
            CodeMirror.commands.autocomplete(cm, null, {
                completeSingle: false
            });
        }, 50);
    } else {
        cm.replaceSelection("<");
    }
},

                "':'": function(cm) {
                    // Auto-trigger for CSS properties
                    if (cm.getMode().name === 'css') {
                        cm.replaceSelection(": ");
                        setTimeout(() => CodeMirror.commands.autocomplete(cm), 50);
                    } else {
                        return CodeMirror.Pass;
                    }
                },
                "Tab": function(cm) {
                    if (cm.somethingSelected()) {
                        cm.indentSelection("add");
                    } else {
                        cm.replaceSelection("  ", "end");
                    }
                }
            },
            hintOptions: {
                completeSingle: false,
                closeCharacters: /[\s()\[\]{};>,]/
            }
        });
        
        editors[qId] = { editor: editor, currentFile: 'index.html' };
        editor.setValue(projectFiles[qId]['index.html']);
        
        let previewTimeout;
        editor.on('change', function() {
            const code = editor.getValue();
            textarea.value = code;
            projectFiles[qId][editors[qId].currentFile] = code;
            
            // Auto-update preview
            clearTimeout(previewTimeout);
            previewTimeout = setTimeout(() => updatePreview(qId), 1000);
        });
        
        // Enhanced autocomplete
        editor.on('inputRead', function(cm, change) {
            if (!cm.state.completionActive) {
                const char = change.text[0];
                const mode = cm.getMode().name;
                
                // HTML: trigger on < or letter after 
                if (mode === "htmlmixed") {
    const cursor = cm.getCursor();
    const token = cm.getTokenAt(cursor);

    if (token.string.startsWith("<") && /<[a-zA-Z0-9-]*$/.test(token.string)) {
        CodeMirror.commands.autocomplete(cm, null, {
            completeSingle: false
        });
    }
}
                
                // CSS: trigger on letters
                if (mode === 'css' && char && char.match(/[a-zA-Z-]/)) {
                    CodeMirror.commands.autocomplete(cm, null, {completeSingle: false});
                }
                
                // JavaScript: trigger on letters and dot
                if (mode === 'javascript' && char && char.match(/[a-zA-Z.]/)) {
                    CodeMirror.commands.autocomplete(cm, null, {completeSingle: false});
                }
            }
        });
        
        // Initial preview
        setTimeout(() => updatePreview(qId), 500);
    });
});

function updatePreview(qId) {
    const frame = document.getElementById('preview-frame-' + qId);
    if (!frame) {
        console.log('Preview frame not found for question', qId);
        return;
    }
    
    const files = projectFiles[qId];
    if (!files) {
        console.log('No files found for question', qId);
        return;
    }
    
    let html = files['index.html'] || '';
    
    // Inject CSS inline
    if (files['styles.css']) {
        const cssTag = '<style>' + files['styles.css'] + '</style>';
        if (html.includes('</head>')) {
            html = html.replace('</head>', cssTag + '</head>');
        } else {
            html = cssTag + html;
        }
    }
    
    // Inject JS inline
    if (files['script.js']) {
    const jsTag = '<script>' + files['script.js'] + '<\/script>';
    if (html.includes('</body>')) {
        html = html.replace('</body>', jsTag + '</body>');
    } else {
        html = html + jsTag;
    }
}

    
    console.log('Updating preview for question', qId);
    
    try {
        const doc = frame.contentDocument || frame.contentWindow.document;
        doc.open();
        doc.write(html);
        doc.close();
    } catch (e) {
        console.error('Preview error:', e);
    }
}

function switchFile(qId, filename) {
    const obj = editors[qId];
    if (!obj) return;
    
    // Save current file
    projectFiles[qId][obj.currentFile] = obj.editor.getValue();
    
    // Switch to new file
    obj.currentFile = filename;
    obj.editor.setValue(projectFiles[qId][filename] || '');
    
    // Update mode
    const modes = { 
        'index.html': 'htmlmixed', 
        'styles.css': 'css', 
        'script.js': 'javascript' 
    };
    obj.editor.setOption('mode', modes[filename]);
    
    // Update active button
    document.querySelectorAll('[data-question="' + qId + '"] .file-tab').forEach(btn => {
        btn.classList.remove('bg-green-600', 'text-white');
        btn.classList.add('bg-gray-700', 'text-gray-300');
    });
    event.target.classList.remove('bg-gray-700', 'text-gray-300');
    event.target.classList.add('bg-green-600', 'text-white');
    
    // Refresh autocomplete for new mode
    setTimeout(() => {
        obj.editor.refresh();
        obj.editor.focus();
    }, 10);
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
                    clearInterval(this.timer);
                    this.submitExam(true);
                }
            }, 1000);
        },

        formatTime(seconds) {
            const h = Math.floor(seconds / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            const s = seconds % 60;
            return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        },

        autoSave() {
            setInterval(() => {
                if (this.timeRemaining > 0 && !this.isSubmitting) {
                    const form = document.getElementById('exam-form');
                    const formData = new FormData(form);
                    
                    for (let [key, value] of formData.entries()) {
                        if (key.startsWith('question_')) {
                            const qId = key.replace('question_', '');
                            this.saveAnswer(qId, value);
                        }
                    }
                }
            }, 30000);
        },

        async saveAnswer(qId, answer) {
            this.isSaving = true;
            try {
                await fetch('<?php echo e(route("student.save-answer", $attempt->id)); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                    },
                    body: JSON.stringify({
                        question_id: qId,
                        answer_text: answer,
                        time_remaining: this.timeRemaining
                    })
                });
                this.lastSaved = true;
                setTimeout(() => this.lastSaved = false, 2000);
            } finally {
                this.isSaving = false;
            }
        },

        async submitExam(auto = false) {
            if (this.isSubmitting) return;
            if (!auto && !confirm('Submit exam? You cannot change your answers after submission.')) return;
            if (auto) alert('Time is up! Submitting automatically.');
            
            this.isSubmitting = true;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?php echo e(route("student.submit-exam", $attempt->id)); ?>';
            form.innerHTML = '<input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>">';
            document.body.appendChild(form);
            form.submit();
        }
    }
}
</script>

<style>
.CodeMirror { 
    height: 350px; 
    border: 1px solid #e5e7eb; 
    font-size: 14px;
}
.CodeMirror-hints { 
    z-index: 10000 !important;
    max-height: 200px;
    overflow-y: auto;
}
.CodeMirror-hint {
    padding: 4px 8px;
    cursor: pointer;
}
.CodeMirror-hint-active {
    background: #16a34a;
    color: white;
}
[x-cloak] { display: none; }
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\cbtplus\modern-cbt-platform-for-highschool\resources\views/student/take-exam.blade.php ENDPATH**/ ?>