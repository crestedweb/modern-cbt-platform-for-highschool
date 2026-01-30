<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamAttempt;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = Auth::user();
        
        // Get available exams for student's class
        $availableExams = Exam::whereHas('classes', function($query) use ($student) {
            $query->where('school_classes.id', $student->class_id);
        })
        ->where('is_active', true)
        ->where('start_date', '<=', now())
        ->where('end_date', '>=', now())
        ->get();

        // Get completed attempts
        $completedAttempts = ExamAttempt::where('user_id', $student->id)
            ->whereIn('status', ['submitted', 'graded'])
            ->with('exam')
            ->latest()
            ->get();

        // Get in-progress attempts
        $inProgressAttempts = ExamAttempt::where('user_id', $student->id)
            ->where('status', 'in_progress')
            ->with('exam')
            ->get();

        return view('student.dashboard', compact('availableExams', 'completedAttempts', 'inProgressAttempts'));
    }

    public function startExam($examId)
    {
        $exam = Exam::findOrFail($examId);
        $student = Auth::user();

        // Check if student already has an attempt
        $existingAttempt = ExamAttempt::where('user_id', $student->id)
            ->where('exam_id', $examId)
            ->whereIn('status', ['in_progress', 'submitted', 'graded'])
            ->first();

        if ($existingAttempt) {
            if ($existingAttempt->isInProgress()) {
                return redirect()->route('student.take-exam', $existingAttempt->id);
            }
            return redirect()->route('student.dashboard')->with('error', 'You have already taken this exam.');
        }

        // Create new attempt
        $attempt = ExamAttempt::create([
            'user_id' => $student->id,
            'exam_id' => $examId,
            'started_at' => now(),
            'time_remaining' => $exam->duration_minutes * 60,
            'status' => 'in_progress',
        ]);

        return redirect()->route('student.take-exam', $attempt->id);
    }

    public function takeExam($attemptId)
    {
        $attempt = ExamAttempt::with(['exam.questions', 'answers'])
            ->findOrFail($attemptId);

        // Check ownership
        if ($attempt->user_id != Auth::id()) {
            abort(403);
        }

        // Check if already submitted
        if ($attempt->isSubmitted() || $attempt->isGraded()) {
            return redirect()->route('student.view-result', $attempt->id);
        }

        $questions = $attempt->exam->questions()->orderBy('order')->get();
        
        return view('student.take-exam', compact('attempt', 'questions'));
    }

    public function saveAnswer(Request $request, $attemptId)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'answer_text' => 'nullable|string',
            'time_remaining' => 'required|integer',
        ]);

        $attempt = ExamAttempt::findOrFail($attemptId);

        // Check ownership
        if ($attempt->user_id != Auth::id()) {
            abort(403);
        }

        // Update time remaining
        $attempt->update(['time_remaining' => $request->time_remaining]);

        // Save or update answer
        $answer = Answer::updateOrCreate(
            [
                'attempt_id' => $attemptId,
                'question_id' => $request->question_id,
            ],
            [
                'answer_text' => $request->answer_text,
            ]
        );

        // Auto-grade if objective question
        $question = $answer->question;
        if ($question->isObjective()) {
            $this->autoGradeAnswer($answer);
        }

        return response()->json(['success' => true, 'message' => 'Answer saved']);
    }

    public function submitExam(Request $request, $attemptId)
    {
        $attempt = ExamAttempt::findOrFail($attemptId);

        // Check ownership
        if ($attempt->user_id != Auth::id()) {
            abort(403);
        }

        DB::transaction(function () use ($attempt) {
            // Mark as submitted
            $attempt->update([
                'submitted_at' => now(),
                'status' => 'submitted',
            ]);

            // Auto-grade objective questions
            $answers = $attempt->answers()->with('question')->get();
            $objectiveScore = 0;

            foreach ($answers as $answer) {
                if ($answer->question->isObjective()) {
                    $this->autoGradeAnswer($answer);
                    if ($answer->is_correct) {
                        $objectiveScore += $answer->marks_obtained;
                    }
                }
            }

            // Update objective score
            $attempt->update(['objective_score' => $objectiveScore]);

            // Check if all questions are objective (auto-gradable)
            $hasSubjective = $answers->filter(function($answer) {
                return !$answer->question->isObjective();
            })->count() > 0;

            if (!$hasSubjective) {
                $attempt->update([
                    'total_score' => $objectiveScore,
                    'status' => 'graded',
                ]);
            }
        });

        return redirect()->route('student.view-result', $attempt->id)
            ->with('success', 'Exam submitted successfully!');
    }

    public function downloadResultPDF($attemptId)
{
    $attempt = ExamAttempt::with(['exam', 'answers.question', 'user'])
        ->findOrFail($attemptId);

    // Check ownership
    if ($attempt->user_id != Auth::id()) {
        abort(403);
    }

    // Only allow download if graded
    if (!$attempt->isGraded()) {
        return redirect()->back()->with('error', 'Results not available yet. Please wait for grading to complete.');
    }

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('student.exports.result-pdf', compact('attempt'));
    
    $filename = 'Result_' . $attempt->exam->title . '_' . $attempt->user->name . '.pdf';
    $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
    
    return $pdf->download($filename);
}

public function downloadResultWord($attemptId)
{
    $attempt = ExamAttempt::with(['exam', 'answers.question', 'user'])
        ->findOrFail($attemptId);

    // Check ownership
    if ($attempt->user_id != Auth::id()) {
        abort(403);
    }

    // Only allow download if graded
    if (!$attempt->isGraded()) {
        return redirect()->back()->with('error', 'Results not available yet. Please wait for grading to complete.');
    }

    $phpWord = new \PhpOffice\PhpWord\PhpWord();
    $section = $phpWord->addSection();

    // Title
    $section->addTitle($attempt->exam->title . ' - Result', 1);
    $section->addTextBreak(1);

    // Student Info
    $section->addText('Student: ' . $attempt->user->name, ['bold' => true]);
    $section->addText('Registration: ' . $attempt->user->registration_number);
    $section->addText('Subject: ' . $attempt->exam->subject);
    $section->addText('Date: ' . $attempt->submitted_at->format('d M Y, h:i A'));
    $section->addTextBreak(1);

    // Score
    $section->addTitle('Score Summary', 2);
    $section->addText('Total Score: ' . $attempt->total_score . '/' . $attempt->exam->total_marks, ['size' => 16, 'bold' => true]);
    $section->addText('Percentage: ' . round(($attempt->total_score / $attempt->exam->total_marks) * 100, 1) . '%');
    $section->addText('Objective Score: ' . ($attempt->objective_score ?? 0));
    $section->addText('Subjective Score: ' . ($attempt->subjective_score ?? 0));
    
    $resultText = $attempt->total_score >= $attempt->exam->pass_mark ? 'PASSED' : 'FAILED';
    $section->addText('Result: ' . $resultText, ['bold' => true, 'color' => $attempt->total_score >= $attempt->exam->pass_mark ? '008000' : 'FF0000']);
    $section->addTextBreak(1);

    // Questions and Answers
    $section->addTitle('Detailed Review', 2);
    
    foreach ($attempt->answers as $index => $answer) {
        $question = $answer->question;
        
        $section->addText('Question ' . ($index + 1) . ':', ['bold' => true]);
        $section->addText($question->question_text);
        $section->addText('Type: ' . ucwords(str_replace('_', ' ', $question->question_type)), ['italic' => true]);
        $section->addText('Marks: ' . $question->marks);
        
        if ($answer->answer_text) {
            $section->addText('Your Answer:', ['bold' => true]);
            $section->addText($answer->answer_text);
        } else {
            $section->addText('Your Answer: Not Answered', ['color' => 'FF0000']);
        }
        
        if ($question->question_type === 'multiple_choice' || $question->question_type === 'fill_blank') {
            $section->addText('Correct Answer: ' . $question->correct_answer, ['color' => '008000']);
        }
        
        $section->addText('Marks Obtained: ' . ($answer->marks_obtained ?? 0) . '/' . $question->marks, ['bold' => true]);
        
        if ($answer->feedback) {
            $section->addText('Teacher Feedback: ' . $answer->feedback, ['italic' => true, 'color' => '0000FF']);
        }
        
        $section->addTextBreak(1);
    }

    $filename = 'Result_' . $attempt->exam->title . '_' . $attempt->user->name . '.docx';
    $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
    $tempFile = storage_path('app/' . $filename);
    
    $writer = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    $writer->save($tempFile);

    return response()->download($tempFile)->deleteFileAfterSend(true);
}

    public function viewResult($attemptId)
    {
        $attempt = ExamAttempt::with(['exam', 'answers.question'])
            ->findOrFail($attemptId);

        // Check ownership
        if ($attempt->user_id != Auth::id()) {
            abort(403);
        }

        return view('student.result', compact('attempt'));
    }

    private function autoGradeAnswer($answer)
    {
        $question = $answer->question;
        
        if ($question->question_type === 'multiple_choice') {
            $isCorrect = strtoupper(trim($answer->answer_text)) === strtoupper(trim($question->correct_answer));
            $answer->update([
                'is_correct' => $isCorrect,
                'marks_obtained' => $isCorrect ? $question->marks : 0,
            ]);
        } elseif ($question->question_type === 'fill_blank') {
            $studentAnswer = strtolower(trim($answer->answer_text));
            $correctAnswer = strtolower(trim($question->correct_answer));
            $isCorrect = $studentAnswer === $correctAnswer;
            
            $answer->update([
                'is_correct' => $isCorrect,
                'marks_obtained' => $isCorrect ? $question->marks : 0,
            ]);
        }
    }
}
