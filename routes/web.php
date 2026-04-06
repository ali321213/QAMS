<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
<<<<<<< Updated upstream
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\StudentController;
use App\Http\Middleware\EnsureUserHasRole;
=======
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
>>>>>>> Stashed changes
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle-block', [AdminController::class, 'toggleBlock'])->name('users.toggle-block');
    });

<<<<<<< Updated upstream
    Route::middleware(EnsureUserHasRole::class.':teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherController::class, 'dashboard'])->name('dashboard');

        Route::get('/quizzes', [TeacherController::class, 'listQuizzes'])->name('quizzes.index');
        Route::get('/quizzes/create', [TeacherController::class, 'createQuiz'])->name('quizzes.create');
        Route::post('/quizzes', [TeacherController::class, 'storeQuiz'])->name('quizzes.store');
        Route::get('/quizzes/{quiz}/edit', [TeacherController::class, 'editQuiz'])->name('quizzes.edit');
        Route::put('/quizzes/{quiz}', [TeacherController::class, 'updateQuiz'])->name('quizzes.update');
        Route::post('/quizzes/{quiz}/publish', [TeacherController::class, 'publishQuiz'])->name('quizzes.publish');
        Route::get('/quizzes/{quiz}/results', [TeacherController::class, 'quizResults'])->name('quizzes.results');

        Route::get('/assignments', [TeacherController::class, 'listAssignments'])->name('assignments.index');
        Route::get('/assignments/create', [TeacherController::class, 'createAssignment'])->name('assignments.create');
        Route::post('/assignments', [TeacherController::class, 'storeAssignment'])->name('assignments.store');
        Route::get('/assignments/{assignment}/edit', [TeacherController::class, 'editAssignment'])->name('assignments.edit');
        Route::put('/assignments/{assignment}', [TeacherController::class, 'updateAssignment'])->name('assignments.update');
        Route::post('/assignments/{assignment}/extend-deadline', [TeacherController::class, 'extendAssignmentDeadline'])->name('assignments.extend-deadline');
        Route::get('/assignments/{assignment}/submissions', [TeacherController::class, 'viewAssignmentSubmissions'])->name('assignments.submissions');
        Route::post('/assignments/{assignment}/grade/{submission}', [TeacherController::class, 'gradeAssignmentSubmission'])->name('assignments.grade');
        Route::post('/assignments/{assignment}/auto-zero-missing', [TeacherController::class, 'autoZeroMissingSubmissions'])->name('assignments.auto-zero');
    });

    Route::middleware(EnsureUserHasRole::class.':student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');

        Route::get('/quizzes', [StudentController::class, 'listQuizzes'])->name('quizzes.index');
        Route::post('/quizzes/{quiz}/start', [StudentController::class, 'startQuiz'])->name('quizzes.start');
        Route::get('/quizzes/{quiz}/question/{index}', [StudentController::class, 'showQuizQuestion'])->name('quizzes.question.show');
        Route::post('/quizzes/{quiz}/question/{index}', [StudentController::class, 'answerQuizQuestion'])->name('quizzes.question.answer');

        Route::get('/assignments', [StudentController::class, 'listAssignments'])->name('assignments.index');
        Route::get('/assignments/{assignment}', [StudentController::class, 'showAssignment'])->name('assignments.show');
        Route::post('/assignments/{assignment}/submit', [StudentController::class, 'submitAssignment'])->name('assignments.submit');

        Route::get('/reports/performance', [StudentController::class, 'performanceReport'])->name('reports.performance');
=======
    Route::middleware('role:admin')->prefix('admin/qams')->group(function () {
        Route::post('/classes', [AdminManagementController::class, 'storeClass']);
        Route::put('/classes/{class}', [AdminManagementController::class, 'updateClass']);
        Route::post('/subjects', [AdminManagementController::class, 'storeSubject']);
        Route::put('/subjects/{subject}', [AdminManagementController::class, 'updateSubject']);
        Route::post('/students', [AdminManagementController::class, 'registerStudent']);
        Route::put('/students/{student}', [AdminManagementController::class, 'updateStudent']);
        Route::post('/teachers', [AdminManagementController::class, 'registerTeacher']);
        Route::put('/teachers/{teacher}', [AdminManagementController::class, 'updateTeacher']);
        Route::post('/subjects/{subject}/assign-teacher', [AdminManagementController::class, 'assignTeacherToSubject']);
        Route::get('/reports', [AdminManagementController::class, 'reports']);
    });

    Route::middleware('role:teacher')->prefix('teacher/qams')->group(function () {
        Route::post('/question-bank', [TeacherController::class, 'storeQuestion']);
        Route::post('/quizzes', [TeacherController::class, 'createQuiz']);
        Route::post('/quizzes/{quiz}/extend', [TeacherController::class, 'extendQuiz']);
        Route::post('/quizzes/{quiz}/publish', [TeacherController::class, 'publishQuiz']);
        Route::post('/assignments', [TeacherController::class, 'createAssignment']);
        Route::post('/assignments/{assignment}/extend', [TeacherController::class, 'extendAssignment']);
        Route::post('/assignments/{assignment}/publish', [TeacherController::class, 'publishAssignment']);
        Route::post('/assignment-submissions/{submission}/grade', [TeacherController::class, 'gradeAssignment']);
        Route::get('/reports/performance', [TeacherController::class, 'performanceReport']);
    });

    Route::middleware('role:student')->prefix('student/qams')->group(function () {
        Route::post('/quizzes/{quiz}/attempt', [StudentController::class, 'attemptQuiz']);
        Route::post('/assignments/{assignment}/submit', [StudentController::class, 'submitAssignment']);
        Route::get('/results', [StudentController::class, 'myResults']);
>>>>>>> Stashed changes
    });
});