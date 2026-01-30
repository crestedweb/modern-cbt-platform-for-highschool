@extends('layouts.app')

@section('title', 'Manage Questions')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">{{ $exam->title }}</h2>
                <p class="text-gray-600">{{ $exam->subject }} - {{ $exam->duration_minutes }} minutes</p>
            </div>
            <a href="{{ route('admin.exams') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded">
                Back to Exams
            </a>
        </div>
        <div class="grid grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-600">Total Questions:</span>
                <p class="font-semibold text-lg">{{ $exam->questions->count() }}</p>
            </div>
            <div>
                <span class="text-gray-600">Total Marks:</span>
                <p class="font-semibold text-lg">{{ $exam->questions->sum('marks') }} / {{ $exam->total_marks }}</p>
            </div>
            <div>
                <span class="text-gray-600">Status:</span>
                <p class="font-semibold text-lg">
                    @if($exam->questions->sum('marks') == $exam->total_marks)
                    <span class="text-green-600">✓ Complete</span>
                    @else
                    <span class="text-yellow-600">⚠ Incomplete</span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Add Question Form -->
    <div class="bg-white rounded-lg shadow p-6" x-data="{ questionType: 'multiple_choice' }">
        <h3 class="text-xl font-bold text-gray-800 mb-4">Add New Question</h3>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

       <form action="{{ route('admin.exam.question.store', $exam->id) }}" method="POST" class="space-y-4">
    @csrf

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
                  placeholder="Enter your question here">{{ old('question_text') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Marks *</label>
        <input type="number" name="marks" value="{{ old('marks', 1) }}" min="1" required
               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
    </div>

    <!-- Multiple Choice Options -->
    <div x-show="questionType === 'multiple_choice'" class="space-y-3">
        <label class="block text-sm font-medium text-gray-700">Options *</label>
        
        <div class="space-y-2">
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">A.</span>
                <input type="text" name="options[A]" value="{{ old('options.A') }}"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option A">
            </div>
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">B.</span>
                <input type="text" name="options[B]" value="{{ old('options.B') }}"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option B">
            </div>
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">C.</span>
                <input type="text" name="options[C]" value="{{ old('options.C') }}"
                       :required="questionType === 'multiple_choice'"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg"
                       placeholder="Option C">
            </div>
            <div class="flex gap-2">
                <span class="font-bold text-gray-700 w-8">D.</span>
                <input type="text" name="options[D]" value="{{ old('options.D') }}"
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
                <option value="A" {{ old('correct_answer') == 'A' ? 'selected' : '' }}>A</option>
                <option value="B" {{ old('correct_answer') == 'B' ? 'selected' : '' }}>B</option>
                <option value="C" {{ old('correct_answer') == 'C' ? 'selected' : '' }}>C</option>
                <option value="D" {{ old('correct_answer') == 'D' ? 'selected' : '' }}>D</option>
            </select>
        </div>
    </div>

    <!-- Fill in the Blank Answer -->
    <template x-if="questionType === 'fill_blank'">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Correct Answer *</label>
            <input type="text" name="correct_answer" value="{{ old('correct_answer') }}"
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
            <h3 class="text-xl font-bold text-gray-800">Questions ({{ $exam->questions->count() }})</h3>
        </div>

        @forelse($exam->questions->sortBy('order') as $index => $question)
        <div class="border-b p-6">
            <div class="flex justify-between items-start mb-3">
                <div class="flex items-start flex-1">
                    <span class="bg-green-600 text-white rounded-full w-8 h-8 flex items-center justify-center font-semibold mr-3 flex-shrink-0">
                        {{ $index + 1 }}
                    </span>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium mb-2">{{ $question->question_text }}</p>
                        <div class="flex gap-2 text-xs">
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">
                                {{ ucwords(str_replace('_', ' ', $question->question_type)) }}
                            </span>
                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">
                                {{ $question->marks }} {{ $question->marks == 1 ? 'mark' : 'marks' }}
                            </span>
                        </div>

                        @if($question->question_type === 'multiple_choice' && $question->options)
                        <div class="mt-3 space-y-1">
                            @foreach($question->options as $key => $option)
                            <p class="text-sm {{ $key === $question->correct_answer ? 'text-green-600 font-semibold' : 'text-gray-600' }}">
                                {{ $key }}. {{ $option }}
                                @if($key === $question->correct_answer)
                                <span class="text-xs">(Correct Answer)</span>
                                @endif
                            </p>
                            @endforeach
                        </div>
                        @endif

                        @if($question->question_type === 'fill_blank')
                        <p class="text-sm text-green-600 font-semibold mt-2">
                            Answer: {{ $question->correct_answer }}
                        </p>
                        @endif
                    </div>
                </div>

                <form action="{{ route('admin.question.delete', $question->id) }}" method="POST" 
                      onsubmit="return confirm('Delete this question?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-600 hover:text-red-800 ml-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <div class="text-gray-400 text-6xl mb-4">❓</div>
            <p class="text-gray-600 text-lg">No questions added yet</p>
            <p class="text-gray-500 text-sm">Use the form above to add your first question</p>
        </div>
        @endforelse
    </div>

    @if($exam->questions->count() > 0)
    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
        <div class="flex justify-between items-center">
            <div>
                <h4 class="font-semibold text-green-800">Exam Ready!</h4>
                <p class="text-sm text-green-600">You have {{ $exam->questions->count() }} questions totaling {{ $exam->questions->sum('marks') }} marks</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.exam.results', $exam->id) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                    View Results
                </a>
                <a href="{{ route('admin.exams') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold">
                    Done
                </a>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection