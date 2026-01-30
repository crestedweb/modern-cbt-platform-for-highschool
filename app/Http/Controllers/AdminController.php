<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\Question;
use App\Models\SchoolClass;
use App\Models\User;
use App\Models\ExamAttempt;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        $examsCount = Exam::when(!$user->isAdmin(), function($query) use ($user) {
            return $query->where('created_by', $user->id);
        })->count();

        $studentsCount = User::where('role', 'student')->count();
        
        $recentExams = Exam::when(!$user->isAdmin(), function($query) use ($user) {
            return $query->where('created_by', $user->id);
        })->latest()->take(5)->get();

        $recentAttempts = ExamAttempt::with(['user', 'exam'])
            ->whereHas('exam', function($query) use ($user) {
                if (!$user->isAdmin()) {
                    $query->where('created_by', $user->id);
                }
            })
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('examsCount', 'studentsCount', 'recentExams', 'recentAttempts'));
    }

    public function exams()
    {
        $user = Auth::user();
        
        $exams = Exam::with(['creator', 'classes'])
            ->when(!$user->isAdmin(), function($query) use ($user) {
                return $query->where('created_by', $user->id);
            })
            ->latest()
            ->get();

        return view('admin.exams.index', compact('exams'));
    }

    public function createExam()
    {
        $classes = SchoolClass::all();
        return view('admin.exams.create', compact('classes'));
    }

    public function storeExam(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject' => 'required|string|max:255',
            'duration_minutes' => 'required|integer|min:1',
            'total_marks' => 'required|integer|min:1',
            'pass_mark' => 'required|integer|min:0',
            'instructions' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'classes' => 'required|array',
            'classes.*' => 'exists:school_classes,id',
        ]);

        $exam = Exam::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'subject' => $validated['subject'],
            'duration_minutes' => $validated['duration_minutes'],
            'total_marks' => $validated['total_marks'],
            'pass_mark' => $validated['pass_mark'],
            'instructions' => $validated['instructions'],
            'created_by' => Auth::id(),
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => true,
        ]);

        $exam->classes()->attach($validated['classes']);

        return redirect()->route('admin.exam.questions', $exam->id)
            ->with('success', 'Exam created successfully! Now add questions.');
    }

    public function editExam($examId)
{
    $exam = Exam::with('classes')->findOrFail($examId);
    
    // Check permission
    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    $classes = SchoolClass::all();
    return view('admin.exams.edit', compact('exam', 'classes'));
}

public function updateExam(Request $request, $examId)
{
    $exam = Exam::findOrFail($examId);
    
    // Check permission
    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'subject' => 'required|string|max:255',
        'duration_minutes' => 'required|integer|min:1',
        'total_marks' => 'required|integer|min:1',
        'pass_mark' => 'required|integer|min:0',
        'instructions' => 'nullable|string',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'classes' => 'required|array',
        'classes.*' => 'exists:school_classes,id',
        'is_active' => 'boolean',
    ]);

    $exam->update([
        'title' => $validated['title'],
        'description' => $validated['description'],
        'subject' => $validated['subject'],
        'duration_minutes' => $validated['duration_minutes'],
        'total_marks' => $validated['total_marks'],
        'pass_mark' => $validated['pass_mark'],
        'instructions' => $validated['instructions'],
        'start_date' => $validated['start_date'],
        'end_date' => $validated['end_date'],
        'is_active' => $request->has('is_active'),
    ]);

    // Sync classes (this will add new ones and remove unchecked ones)
    $exam->classes()->sync($validated['classes']);

    return redirect()->route('admin.exams')
        ->with('success', 'Exam updated successfully!');
}

    public function examQuestions($examId)
    {
        $exam = Exam::with('questions')->findOrFail($examId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
            abort(403);
        }

        return view('admin.exams.questions', compact('exam'));
    }

    public function storeQuestion(Request $request, $examId)
{
    $exam = Exam::findOrFail($examId);
    
    // Check permission
    if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
        abort(403);
    }

    // Basic validation
    $validated = $request->validate([
        'question_text' => 'required|string',
        'question_type' => 'required|in:multiple_choice,theory,coding,fill_blank',
        'marks' => 'required|integer|min:1',
    ]);

    // Type-specific validation
    if ($request->question_type === 'multiple_choice') {
        $request->validate([
            'options' => 'required|array|min:4',
            'options.A' => 'required|string',
            'options.B' => 'required|string',
            'options.C' => 'required|string',
            'options.D' => 'required|string',
            'correct_answer' => 'required|in:A,B,C,D',
        ]);
        $validated['options'] = $request->options;
        $validated['correct_answer'] = $request->correct_answer;
    } elseif ($request->question_type === 'fill_blank') {
        $request->validate([
            'correct_answer' => 'required|string',
        ]);
        $validated['correct_answer'] = $request->correct_answer;
        $validated['options'] = null;
    } else {
        // Theory and Coding don't need correct_answer or options
        $validated['correct_answer'] = null;
        $validated['options'] = null;
    }

    $order = $exam->questions()->max('order') + 1;

    Question::create([
        'exam_id' => $examId,
        'question_text' => $validated['question_text'],
        'question_type' => $validated['question_type'],
        'marks' => $validated['marks'],
        'options' => $validated['options'] ?? null,
        'correct_answer' => $validated['correct_answer'] ?? null,
        'order' => $order,
    ]);

    return redirect()->back()->with('success', 'Question added successfully!');
}

    public function deleteQuestion($questionId)
    {
        $question = Question::findOrFail($questionId);
        $exam = $question->exam;
        
        // Check permission
        if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
            abort(403);
        }

        $question->delete();

        return redirect()->back()->with('success', 'Question deleted successfully!');
    }

    public function examResults($examId)
    {
        $exam = Exam::with(['attempts.user', 'attempts.answers'])
            ->findOrFail($examId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $exam->created_by != Auth::id()) {
            abort(403);
        }

        $attempts = $exam->attempts()
            ->whereIn('status', ['submitted', 'graded'])
            ->with('user')
            ->get();

        // Calculate statistics
        $gradedAttempts = $attempts->where('status', 'graded');
        $scores = $gradedAttempts->pluck('total_score')->filter();
        
        $statistics = [
            'total_students' => $attempts->count(),
            'graded' => $gradedAttempts->count(),
            'pending' => $attempts->where('status', 'submitted')->count(),
            'average' => $scores->count() > 0 ? round($scores->average(), 2) : 0,
            'highest' => $scores->count() > 0 ? $scores->max() : 0,
            'lowest' => $scores->count() > 0 ? $scores->min() : 0,
            'pass_rate' => $gradedAttempts->count() > 0 
                ? round(($gradedAttempts->where('total_score', '>=', $exam->pass_mark)->count() / $gradedAttempts->count()) * 100, 2)
                : 0,
        ];

        return view('admin.exams.results', compact('exam', 'attempts', 'statistics'));
    }

    public function gradeAttempt($attemptId)
    {
        $attempt = ExamAttempt::with(['exam', 'user', 'answers.question'])
            ->findOrFail($attemptId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $attempt->exam->created_by != Auth::id()) {
            abort(403);
        }

        return view('admin.exams.grade', compact('attempt'));
    }

    public function updateGrading(Request $request, $attemptId)
    {
        $attempt = ExamAttempt::with(['answers'])->findOrFail($attemptId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $attempt->exam->created_by != Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'grades' => 'required|array',
            'grades.*.answer_id' => 'required|exists:answers,id',
            'grades.*.marks_obtained' => 'required|numeric|min:0',
            'grades.*.feedback' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $attempt) {
            $subjectiveScore = 0;

            foreach ($validated['grades'] as $gradeData) {
                $answer = Answer::findOrFail($gradeData['answer_id']);
                
                $answer->update([
                    'marks_obtained' => $gradeData['marks_obtained'],
                    'feedback' => $gradeData['feedback'] ?? null,
                    'graded_by' => Auth::id(),
                ]);

                $subjectiveScore += $gradeData['marks_obtained'];
            }

            $totalScore = ($attempt->objective_score ?? 0) + $subjectiveScore;

            $attempt->update([
                'subjective_score' => $subjectiveScore,
                'total_score' => $totalScore,
                'status' => 'graded',
            ]);
        });

        return redirect()->route('admin.exam.results', $attempt->exam_id)
            ->with('success', 'Grading completed successfully!');
    }

    public function exportResultsPDF($examId)
    {
        $exam = Exam::with(['attempts' => function($query) {
            $query->where('status', 'graded')->with('user');
        }])->findOrFail($examId);

        $attempts = $exam->attempts;
        $scores = $attempts->pluck('total_score');
        
        $statistics = [
            'average' => $scores->count() > 0 ? round($scores->average(), 2) : 0,
            'highest' => $scores->count() > 0 ? $scores->max() : 0,
            'lowest' => $scores->count() > 0 ? $scores->min() : 0,
        ];

        $pdf = Pdf::loadView('admin.exports.results-pdf', compact('exam', 'attempts', 'statistics'));
        
        return $pdf->download($exam->title . '_results.pdf');
    }

    public function exportResultsWord($examId)
    {
        $exam = Exam::with(['attempts' => function($query) {
            $query->where('status', 'graded')->with('user');
        }])->findOrFail($examId);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        // Title
        $section->addTitle($exam->title . ' - Results', 1);
        $section->addText('Subject: ' . $exam->subject);
        $section->addText('Date: ' . now()->format('d M Y'));
        $section->addTextBreak(1);

        // Statistics
        $scores = $exam->attempts->pluck('total_score');
        $section->addTitle('Statistics', 2);
        $section->addText('Total Students: ' . $exam->attempts->count());
        $section->addText('Average Score: ' . ($scores->count() > 0 ? round($scores->average(), 2) : 0));
        $section->addText('Highest Score: ' . ($scores->count() > 0 ? $scores->max() : 0));
        $section->addText('Lowest Score: ' . ($scores->count() > 0 ? $scores->min() : 0));
        $section->addTextBreak(1);

        // Results table
        $section->addTitle('Student Results', 2);
        $table = $section->addTable(['borderSize' => 6, 'borderColor' => '000000']);
        
        // Header
        $table->addRow();
        $table->addCell(2000)->addText('Student Name');
        $table->addCell(2000)->addText('Registration No.');
        $table->addCell(2000)->addText('Score');
        $table->addCell(2000)->addText('Grade');

        // Data
        foreach ($exam->attempts as $attempt) {
            $table->addRow();
            $table->addCell(2000)->addText($attempt->user->name);
            $table->addCell(2000)->addText($attempt->user->registration_number);
            $table->addCell(2000)->addText($attempt->total_score . '/' . $exam->total_marks);
            $table->addCell(2000)->addText($attempt->total_score >= $exam->pass_mark ? 'Pass' : 'Fail');
        }

        $filename = $exam->title . '_results.docx';
        $tempFile = storage_path('app/' . $filename);
        
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($tempFile);

        return response()->download($tempFile)->deleteFileAfterSend(true);
    }

    public function printScript($attemptId)
    {
        $attempt = ExamAttempt::with(['exam.questions', 'user', 'answers'])
            ->findOrFail($attemptId);
        
        // Check permission
        if (!Auth::user()->isAdmin() && $attempt->exam->created_by != Auth::id()) {
            abort(403);
        }

        return view('admin.exports.print-script', compact('attempt'));
    }

    // Teacher Management
public function teachers()
{
    $teachers = User::where('role', 'teacher')->with('exams')->get();
    return view('admin.teachers.index', compact('teachers'));
}

public function createTeacher()
{
    return view('admin.teachers.create');
}

public function storeTeacher(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'registration_number' => 'required|string|unique:users,registration_number',
        'password' => 'required|string|min:6',
    ]);

    User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'registration_number' => $validated['registration_number'],
        'password' => Hash::make($validated['password']),
        'role' => 'teacher',
    ]);

    return redirect()->route('admin.teachers')->with('success', 'Teacher added successfully!');
}

public function editTeacher($teacherId)
{
    $teacher = User::where('role', 'teacher')->findOrFail($teacherId);
    return view('admin.teachers.edit', compact('teacher'));
}

public function updateTeacher(Request $request, $teacherId)
{
    $teacher = User::where('role', 'teacher')->findOrFail($teacherId);
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $teacherId,
        'registration_number' => 'required|string|unique:users,registration_number,' . $teacherId,
        'password' => 'nullable|string|min:6',
    ]);

    $teacher->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'registration_number' => $validated['registration_number'],
    ]);

    if ($request->filled('password')) {
        $teacher->update(['password' => Hash::make($validated['password'])]);
    }

    return redirect()->route('admin.teachers')->with('success', 'Teacher updated successfully!');
}

public function deleteTeacher($teacherId)
{
    $teacher = User::where('role', 'teacher')->findOrFail($teacherId);
    $teacher->delete();
    
    return redirect()->route('admin.teachers')->with('success', 'Teacher deleted successfully!');
}

// Class Management
public function classes()
{
    $classes = SchoolClass::withCount(['students', 'exams'])->get();
    return view('admin.classes.index', compact('classes'));
}

public function storeClass(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255|unique:school_classes,name',
        'description' => 'nullable|string',
    ]);

    SchoolClass::create($validated);

    return redirect()->route('admin.classes')->with('success', 'Class added successfully!');
}

public function deleteClass($classId)
{
    $class = SchoolClass::findOrFail($classId);
    
    // Check if class has students
    if ($class->students()->count() > 0) {
        return redirect()->back()->with('error', 'Cannot delete class with enrolled students!');
    }
    
    $class->delete();
    
    return redirect()->route('admin.classes')->with('success', 'Class deleted successfully!');
}

// Student Management
public function students()
{
    $students = User::where('role', 'student')
        ->with('class')
        ->orderBy('registration_number')
        ->get();
    return view('admin.students.index', compact('students'));
}

public function createStudent()
{
    $classes = SchoolClass::all();
    return view('admin.students.create', compact('classes'));
}

public function storeStudent(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'registration_number' => 'required|string|unique:users,registration_number',
        'class_id' => 'required|exists:school_classes,id',
        'password' => 'required|string|min:6',
    ]);

    User::create([
        'name' => $validated['name'],
        'registration_number' => $validated['registration_number'],
        'class_id' => $validated['class_id'],
        'password' => Hash::make($validated['password']),
        'role' => 'student',
    ]);

    return redirect()->route('admin.students')->with('success', 'Student added successfully!');
}

public function editStudent($studentId)
{
    $student = User::where('role', 'student')->findOrFail($studentId);
    $classes = SchoolClass::all();
    return view('admin.students.edit', compact('student', 'classes'));
}

public function updateStudent(Request $request, $studentId)
{
    $student = User::where('role', 'student')->findOrFail($studentId);
    
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'registration_number' => 'required|string|unique:users,registration_number,' . $studentId,
        'class_id' => 'required|exists:school_classes,id',
        'password' => 'nullable|string|min:6',
    ]);

    $student->update([
        'name' => $validated['name'],
        'registration_number' => $validated['registration_number'],
        'class_id' => $validated['class_id'],
    ]);

    if ($request->filled('password')) {
        $student->update(['password' => Hash::make($validated['password'])]);
    }

    return redirect()->route('admin.students')->with('success', 'Student updated successfully!');
}

public function deleteStudent($studentId)
{
    $student = User::where('role', 'student')->findOrFail($studentId);
    $student->delete();
    
    return redirect()->route('admin.students')->with('success', 'Student deleted successfully!');
}
}
