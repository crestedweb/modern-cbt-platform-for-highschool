<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Student routes
    Route::prefix('student')->name('student.')->middleware('role:student')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');
        Route::get('/exam/{exam}/start', [StudentController::class, 'startExam'])->name('start-exam');
        Route::get('/attempt/{attempt}', [StudentController::class, 'takeExam'])->name('take-exam');
        Route::post('/attempt/{attempt}/save', [StudentController::class, 'saveAnswer'])->name('save-answer');
        Route::post('/attempt/{attempt}/submit', [StudentController::class, 'submitExam'])->name('submit-exam');
        Route::get('/attempt/{attempt}/result', [StudentController::class, 'viewResult'])->name('view-result');
        Route::get('/attempt/{attempt}/result', [StudentController::class, 'viewResult'])->name('view-result');
Route::get('/attempt/{attempt}/download-pdf', [StudentController::class, 'downloadResultPDF'])->name('download-result-pdf');
Route::get('/attempt/{attempt}/download-word', [StudentController::class, 'downloadResultWord'])->name('download-result-word');
    });

    // Admin/Teacher routes
    // Admin/Teacher routes
Route::prefix('admin')->name('admin.')->middleware('role:admin,teacher')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Teacher Management (Admin Only)
    // Student Management (Admin Only)
Route::middleware('role:admin')->group(function () {
    Route::get('/teachers', [AdminController::class, 'teachers'])->name('teachers');
    Route::get('/teachers/create', [AdminController::class, 'createTeacher'])->name('teacher.create');
    Route::post('/teachers', [AdminController::class, 'storeTeacher'])->name('teacher.store');
    Route::get('/teachers/{teacher}/edit', [AdminController::class, 'editTeacher'])->name('teacher.edit');
    Route::put('/teachers/{teacher}', [AdminController::class, 'updateTeacher'])->name('teacher.update');
    Route::delete('/teachers/{teacher}', [AdminController::class, 'deleteTeacher'])->name('teacher.delete');
    
    // Add these new student routes
    Route::get('/students', [AdminController::class, 'students'])->name('students');
    Route::get('/students/create', [AdminController::class, 'createStudent'])->name('student.create');
    Route::post('/students', [AdminController::class, 'storeStudent'])->name('student.store');
    Route::get('/students/{student}/edit', [AdminController::class, 'editStudent'])->name('student.edit');
    Route::put('/students/{student}', [AdminController::class, 'updateStudent'])->name('student.update');
    Route::delete('/students/{student}', [AdminController::class, 'deleteStudent'])->name('student.delete');
    
    // Class Management
    Route::get('/classes', [AdminController::class, 'classes'])->name('classes');
        Route::post('/classes', [AdminController::class, 'storeClass'])->name('class.store');
        Route::delete('/classes/{class}', [AdminController::class, 'deleteClass'])->name('class.delete');
    });
    
    // Exams (existing routes)
        
        // Exams
        Route::get('/exams', [AdminController::class, 'exams'])->name('exams');
        Route::get('/exams/create', [AdminController::class, 'createExam'])->name('exam.create');
        Route::post('/exams', [AdminController::class, 'storeExam'])->name('exam.store');
        Route::get('/exams/{exam}/edit', [AdminController::class, 'editExam'])->name('exam.edit');
Route::put('/exams/{exam}', [AdminController::class, 'updateExam'])->name('exam.update');
        Route::get('/exams/{exam}/questions', [AdminController::class, 'examQuestions'])->name('exam.questions');
        Route::post('/exams/{exam}/questions', [AdminController::class, 'storeQuestion'])->name('exam.question.store');
        Route::delete('/questions/{question}', [AdminController::class, 'deleteQuestion'])->name('question.delete');
        
        // Results & Grading
        Route::get('/exams/{exam}/results', [AdminController::class, 'examResults'])->name('exam.results');
        Route::get('/attempts/{attempt}/grade', [AdminController::class, 'gradeAttempt'])->name('attempt.grade');
        Route::post('/attempts/{attempt}/grade', [AdminController::class, 'updateGrading'])->name('attempt.update-grade');
        
        // Exports
        Route::get('/exams/{exam}/export/pdf', [AdminController::class, 'exportResultsPDF'])->name('exam.export.pdf');
        Route::get('/exams/{exam}/export/word', [AdminController::class, 'exportResultsWord'])->name('exam.export.word');
        Route::get('/attempts/{attempt}/print', [AdminController::class, 'printScript'])->name('attempt.print');
    });
});
