@extends('layouts.app')
@section('title', 'Student Dashboard')
@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-2">Welcome, {{ auth()->user()->name }}!</h2>
        <p class="text-gray-600">Registration Number: {{ auth()->user()->registration_number }}</p>
        <p class="text-gray-600">Class: {{ auth()->user()->class->name ?? 'N/A' }}</p>
    </div>

    @if($inProgressAttempts->count() > 0)
    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
        <h3 class="font-semibold text-yellow-800 mb-2">⚠️ Exams In Progress</h3>
        @foreach($inProgressAttempts as $attempt)
        <div class="flex justify-between items-center bg-white p-3 rounded mb-2">
            <div>
                <p class="font-medium">{{ $attempt->exam->title }}</p>
                <p class="text-sm text-gray-600">Started: {{ $attempt->started_at->format('d M Y, h:i A') }}</p>
            </div>
            <a href="{{ route('student.take-exam', $attempt->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded">Continue Exam</a>
        </div>
        @endforeach
    </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-800">📝 Available Exams</h3>
        </div>
        <div class="p-6">
            @forelse($availableExams as $exam)
            <div class="border rounded-lg p-4 mb-4 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-3">
                    <div class="flex-1">
                        <h4 class="text-lg font-semibold text-gray-800">{{ $exam->title }}</h4>
                        <p class="text-gray-600 text-sm mt-1">{{ $exam->description }}</p>
                    </div>
                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full">{{ $exam->subject }}</span>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div class="text-sm">
                        <span class="text-gray-600">Duration:</span>
                        <p class="font-semibold">{{ $exam->duration_minutes }} mins</p>
                    </div>
                    <div class="text-sm">
                        <span class="text-gray-600">Total Marks:</span>
                        <p class="font-semibold">{{ $exam->total_marks }}</p>
                    </div>
                    <div class="text-sm">
                        <span class="text-gray-600">Pass Mark:</span>
                        <p class="font-semibold">{{ $exam->pass_mark }}</p>
                    </div>
                    <div class="text-sm">
                        <span class="text-gray-600">Questions:</span>
                        <p class="font-semibold">{{ $exam->questions->count() }}</p>
                    </div>
                </div>
                @if($exam->instructions)
                <div class="bg-blue-50 p-3 rounded mb-3">
                    <p class="text-sm text-gray-700"><strong>Instructions:</strong> {{ $exam->instructions }}</p>
                </div>
                @endif
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">Available until: {{ $exam->end_date->format('d M Y') }}</div>
                    <a href="{{ route('student.start-exam', $exam->id) }}" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">Start Exam</a>
                </div>
            </div>
            @empty
            <p class="text-gray-600 text-center py-8">No exams available at the moment.</p>
            @endforelse
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-800">📊 Exam History</h3>
        </div>
        <div class="p-6">
            @forelse($completedAttempts as $attempt)
            <div class="border rounded-lg p-4 mb-3 flex justify-between items-center">
                <div>
                    <h4 class="font-semibold text-gray-800">{{ $attempt->exam->title }}</h4>
                    <p class="text-sm text-gray-600">Submitted: {{ $attempt->submitted_at->format('d M Y, h:i A') }}</p>
                    @if($attempt->isGraded())
                    <p class="text-sm mt-1">
                        <span class="font-semibold">Score:</span> 
                        <span class="text-lg font-bold {{ $attempt->total_score >= $attempt->exam->pass_mark ? 'text-green-600' : 'text-red-600' }}">
                            {{ $attempt->total_score }}/{{ $attempt->exam->total_marks }}
                        </span>
                    </p>
                    @else
                    <span class="inline-block bg-yellow-100 text-yellow-800 text-xs px-2 py-1 rounded mt-1">Pending Grading</span>
                    @endif
                </div>
                <a href="{{ route('student.view-result', $attempt->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">View Details</a>
            </div>
            @empty
            <p class="text-gray-600 text-center py-8">No completed exams yet.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
