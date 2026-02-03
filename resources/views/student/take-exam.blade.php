@extends('layouts.app')

@section('title', 'Take Exam')

@section('content')
<div x-data="examApp()" x-init="init()" class="space-y-6">
    <!-- Timer and Header -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $attempt->exam->title }}</h2>
                <p class="text-gray-600">{{ $attempt->exam->subject }}</p>
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
        @csrf
        <div class="bg-white rounded-lg shadow p-6 space-y-8">
            @foreach($questions as $index => $question)
            <div class="border-b pb-6 last:border-b-0" id="question-{{ $question->id }}">
                <div class="flex items-start mb-4">
                    <span class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-semibold mr-3 flex-shrink-0">
                        {{ $index + 1 }}
                    </span>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium mb-2">{{ $question->question_text }}</p>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                            {{ $question->marks }} {{ $question->marks == 1 ? 'mark' : 'marks' }}
                        </span>
                        <span class="text-xs bg-gray-100 text-gray-800 px-2 py-1 rounded ml-2">
                            {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                        </span>
                    </div>
                </div>

                @php
                    $savedAnswer = $attempt->answers->where('question_id', $question->id)->first();
                @endphp

                <div class="ml-11">
                    <div class="ml-11">
    <!-- Reference Image (if uploaded by teacher) -->
    @if($question->image_path)
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
            <img src="{{ $question->getImageUrl() }}" 
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
                <img src="{{ $question->getImageUrl() }}" 
                     alt="Reference design zoomed" 
                     class="max-w-full max-h-screen object-contain rounded-lg shadow-xl">
            </div>
        </div>
    </div>
    @endif

    @if($question->question_type === 'multiple_choice')
                    @if($question->question_type === 'multiple_choice')
                        <!-- Multiple Choice -->
                        <div class="space-y-2">
                            @foreach($question->options as $key => $option)
                            <label class="flex items-center p-3 border rounded hover:bg-gray-50 cursor-pointer transition">
                                <input 
                                    type="radio" 
                                    name="question_{{ $question->id }}" 
                                    value="{{ $key }}"
                                    {{ $savedAnswer && $savedAnswer->answer_text == $key ? 'checked' : '' }}
                                    @change="saveAnswer({{ $question->id }}, $event.target.value)"
                                    class="mr-3 h-4 w-4 text-green-600"
                                >
                                <span><strong>{{ $key }}.</strong> {{ $option }}</span>
                            </label>
                            @endforeach
                        </div>

                    @elseif($question->question_type === 'fill_blank')
                        <!-- Fill in the Blank -->
                        <input 
                            type="text" 
                            name="question_{{ $question->id }}"
                            value="{{ $savedAnswer->answer_text ?? '' }}"
                            @change="saveAnswer({{ $question->id }}, $event.target.value)"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Type your answer here..."
                        >

                    @elseif($question->question_type === 'theory')
                        <!-- Theory/Essay -->
                        <textarea 
                            name="question_{{ $question->id }}"
                            rows="8"
                            @change="saveAnswer({{ $question->id }}, $event.target.value)"
                            class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500"
                            placeholder="Write your answer here..."
                        >{{ $savedAnswer->answer_text ?? '' }}</textarea>

                    @elseif($question->question_type === 'coding')
    <!-- Coding with Live Preview -->
    <div x-data="{ showPreview: false }">
        <div class="bg-gray-800 text-gray-200 p-2 rounded-t text-xs font-mono flex justify-between items-center">
    <span>Code Editor - Write your code below</span>
    <div class="flex gap-2 items-center">
        <!-- Example Template Dropdown -->
        <select id="template-select-{{ $question->id }}" 
                class="bg-gray-700 text-white text-xs px-2 py-1 rounded border-0"
                onchange="loadTemplate({{ $question->id }}, this.value)">
            <option value="">Load Template...</option>
            <option value="html-basic">HTML Basic</option>
            <option value="html-form">HTML Form</option>
            <option value="css-card">CSS Card</option>
            <option value="js-alert">JS Alert</option>
        </select>
        
        <select id="language-select-{{ $question->id }}" 
                class="bg-gray-700 text-white text-xs px-2 py-1 rounded border-0">
            <option value="html">HTML</option>
            <option value="css">CSS</option>
            <option value="javascript">JavaScript</option>
            <option value="python">Python</option>
            <option value="php">PHP</option>
        </select>
        
        <button type="button" 
                @click="showPreview = !showPreview"
                class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1 rounded">
            <span x-show="!showPreview">👁️ Show Preview</span>
            <span x-show="showPreview">📝 Hide Preview</span>
        </button>
    </div>
</div>

        <!-- Split View: Code + Preview -->
        <div class="grid" :class="showPreview ? 'grid-cols-2 gap-2' : 'grid-cols-1'">
            <!-- Code Editor -->
            <div>
                <textarea 
                    id="code-editor-{{ $question->id }}"
                    name="question_{{ $question->id }}"
                    rows="15"
                    class="w-full px-4 py-2 border border-t-0 font-mono text-sm focus:ring-2 focus:ring-green-500 bg-gray-50 code-editor"
                    :class="showPreview ? 'rounded-bl-lg' : 'rounded-b-lg'"
                    placeholder="// Write your code here..."
                    data-question-id="{{ $question->id }}"
                >{{ $savedAnswer->answer_text ?? '' }}</textarea>
            </div>

            <!-- Live Preview Panel -->
            <div x-show="showPreview" class="border border-t-0 rounded-br-lg overflow-hidden">
                <div class="bg-gray-100 p-2 border-b flex justify-between items-center">
                    <span class="text-xs font-semibold text-gray-700">Live Preview</span>
                    <button type="button" 
                            @click="document.getElementById('preview-frame-{{ $question->id }}').contentWindow.location.reload()"
                            class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">
                        🔄 Refresh
                    </button>
                </div>
                <iframe 
                    id="preview-frame-{{ $question->id }}"
                    class="w-full bg-white"
                    style="height: 400px; border: none;"
                    sandbox="allow-scripts"
                ></iframe>
            </div>
        </div>
    </div>
@endif
                </div>
            </div>
            @endforeach
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

@push('scripts')

<!-- CodeMirror CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/theme/monokai.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.css">

<!-- CodeMirror JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/python/python.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/php/php.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/mode/xml/xml.min.js"></script>

<!-- CodeMirror Addons for Autocomplete -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/show-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/html-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/css-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/javascript-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/hint/xml-hint.min.js"></script>

<!-- Auto Brackets and Tags -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/closetag.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchtags.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.16/addon/edit/matchbrackets.min.js"></script>

<!-- Initialize CodeMirror -->
<!-- Initialize CodeMirror -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find all code editor textareas
    const codeEditors = document.querySelectorAll('.code-editor');
    const editors = {};
    
    codeEditors.forEach(function(textarea) {
        const questionId = textarea.dataset.questionId;
        const languageSelect = document.getElementById('language-select-' + questionId);
        const previewFrame = document.getElementById('preview-frame-' + questionId);
        
        // Initialize CodeMirror
        const editor = CodeMirror.fromTextArea(textarea, {
            mode: 'htmlmixed',
            theme: 'monokai',
            lineNumbers: true,
            autoCloseBrackets: true,
            autoCloseTags: true,
            matchBrackets: true,
            matchTags: true,
            indentUnit: 4,
            indentWithTabs: false,
            lineWrapping: true,
            extraKeys: {
                "Ctrl-Space": "autocomplete",
                "Ctrl-Enter": function(cm) {
                    // Run code preview on Ctrl+Enter
                    updatePreview(questionId);
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
        
        // Store editor instance
        editors[questionId] = editor;
        
        // Function to update preview
        function updatePreview(qId) {
            const code = editors[qId].getValue();
            const frame = document.getElementById('preview-frame-' + qId);
            
            if (frame) {
                const doc = frame.contentDocument || frame.contentWindow.document;
                doc.open();
                doc.write(code);
                doc.close();
            }
        }
        
        // Auto-save and update preview on change
        let updateTimeout;
        editor.on('change', function() {
            const code = editor.getValue();
            textarea.value = code;
            
            // Clear previous timeout
            clearTimeout(updateTimeout);
            
            // Update preview after 1 second of no typing (debounce)
            updateTimeout = setTimeout(function() {
                updatePreview(questionId);
            }, 1000);
            
            // Trigger auto-save
            if (window.examApp) {
                const app = Alpine.$data(document.querySelector('[x-data]'));
                if (app && app.saveAnswer) {
                    app.saveAnswer(questionId, code);
                }
            }
        });
        
        // Language mode switcher
        if (languageSelect) {
            languageSelect.addEventListener('change', function() {
                const mode = this.value;
                const modeMap = {
                    'html': 'htmlmixed',
                    'css': 'css',
                    'javascript': 'javascript',
                    'python': 'python',
                    'php': 'php'
                };
                editor.setOption('mode', modeMap[mode] || 'htmlmixed');
                
                // Update preview for HTML/CSS/JS
                if (['html', 'css', 'javascript'].includes(mode)) {
                    updatePreview(questionId);
                }
            });
        }
        
        // Trigger autocomplete on input
        editor.on('inputRead', function(cm, change) {
            if (!cm.state.completionActive && change.text[0].match(/[a-zA-Z<]/)) {
                CodeMirror.commands.autocomplete(cm, null, {completeSingle: false});
            }
        });
        
        // Initial preview load
        if (previewFrame && textarea.value.trim()) {
            updatePreview(questionId);
        }
    });
});
</script>
<script>
// Code Templates
const codeTemplates = {
    'html-basic': `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Page</title>
</head>
<body>
    <h1>Hello World!</h1>
    <p>This is a paragraph.</p>
</body>
</html>`,
    
    'html-form': `<!DOCTYPE html>
<html>
<head>
    <title>Form Example</title>
</head>
<body>
    <form>
        <label>Name:</label>
        <input type="text" name="name" placeholder="Enter your name">
        <br><br>
        <label>Email:</label>
        <input type="email" name="email" placeholder="Enter your email">
        <br><br>
        <button type="submit">Submit</button>
    </form>
</body>
</html>`,
    
    'css-card': `<!DOCTYPE html>
<html>
<head>
    <style>
        .card {
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .card h2 {
            color: #333;
            margin-top: 0;
        }
        .card p {
            color: #666;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Card Title</h2>
        <p>This is a card with some text.</p>
    </div>
</body>
</html>`,
    
    'js-alert': `<!DOCTYPE html>
<html>
<head>
    <title>JavaScript Example</title>
</head>
<body>
    <button onclick="showMessage()">Click Me!</button>
    
    <script>
        function showMessage() {
            alert('Hello from JavaScript!');
        }
    </script>
</body>
</html>`
};

function loadTemplate(questionId, templateKey) {
    if (!templateKey) return;
    
    const template = codeTemplates[templateKey];
    if (template) {
        // Find the CodeMirror editor
        const textareas = document.querySelectorAll('.code-editor');
        textareas.forEach(function(textarea) {
            if (textarea.dataset.questionId == questionId) {
                const cm = textarea.nextSibling;
                if (cm && cm.CodeMirror) {
                    cm.CodeMirror.setValue(template);
                }
            }
        });
    }
    
    // Reset select
    document.getElementById('template-select-' + questionId).value = '';
}
</script>

<script>
function examApp() {
    return {
        timeRemaining: {{ $attempt->time_remaining ?? ($attempt->exam->duration_minutes * 60) }},
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
                const response = await fetch('{{ route("student.save-answer", $attempt->id) }}', {
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
            form.action = '{{ route("student.submit-exam", $attempt->id) }}';
            
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
    height: 400px;
    font-size: 14px;
    border: 1px solid #e5e7eb;
    border-top: none;
}

.grid-cols-2 .CodeMirror {
    border-right: none;
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

/* Preview frame styling */
#preview-frame {
    background: white;
}
</style>

@endpush
@endsection