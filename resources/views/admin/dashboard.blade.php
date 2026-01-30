@extends('layouts.app')
@section('title', 'Admin Dashboard')
@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Admin Dashboard</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-green-100 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-green-800">Total Exams</h3>
                <p class="text-3xl font-bold text-green-600">{{ $examsCount }}</p>
            </div>
            <div class="bg-blue-100 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-blue-800">Total Students</h3>
                <p class="text-3xl font-bold text-blue-600">{{ $studentsCount }}</p>
            </div>
            <div class="bg-purple-100 p-6 rounded-lg">
                <h3 class="text-lg font-semibold text-purple-800">Recent Attempts</h3>
                <p class="text-3xl font-bold text-purple-600">{{ $recentAttempts->count() }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.exam.create') }}" class="block bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded text-center font-semibold">Create New Exam</a>
                <a href="{{ route('admin.exams') }}" class="block bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded text-center font-semibold">View All Exams</a>
            </div>
             <div class="space-y-2">
        @if(auth()->user()->isAdmin())
    <a href="{{ route('admin.students') }}" class="block bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-3 rounded text-center font-semibold">Manage Students</a>
    <a href="{{ route('admin.teachers') }}" class="block bg-purple-600 hover:bg-purple-700 text-white px-4 py-3 rounded text-center font-semibold">Manage Teachers</a>
    <a href="{{ route('admin.classes') }}" class="block bg-orange-600 hover:bg-orange-700 text-white px-4 py-3 rounded text-center font-semibold">Manage Classes</a>
    @endif
    </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Recent Exams</h3>
            <div class="space-y-2">
                @forelse($recentExams as $exam)
                <div class="border-l-4 border-green-500 pl-3 py-2">
                    <p class="font-semibold">{{ $exam->title }}</p>
                    <p class="text-sm text-gray-600">{{ $exam->subject }} - {{ $exam->questions->count() }} questions</p>
                </div>
                @empty
                <p class="text-gray-600">No exams yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <h3 class="text-xl font-bold text-gray-800">Recent Student Attempts</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($recentAttempts as $attempt)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $attempt->user->name }}</td>
                        <td class="px-6 py-4">{{ $attempt->exam->title }}</td>
                        <td class="px-6 py-4">
                            @if($attempt->status === 'graded')
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded">Graded</span>
                            @elseif($attempt->status === 'submitted')
                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded">Pending</span>
                            @else
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded">In Progress</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ $attempt->total_score ?? '-' }}</td>
                        <td class="px-6 py-4">{{ $attempt->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4">
                            @if($attempt->status === 'submitted')
                            <a href="{{ route('admin.attempt.grade', $attempt->id) }}" class="text-blue-600 hover:text-blue-800">Grade</a>
                            @else
                            <a href="{{ route('admin.exam.results', $attempt->exam_id) }}" class="text-gray-600 hover:text-gray-800">View</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-600">No attempts yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
