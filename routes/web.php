<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminManagementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
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

    Route::middleware('role:teacher')->group(function () {
        Route::get('/teacher/dashboard', [TeacherController::class, 'dashboard'])->name('teacher.dashboard');
        Route::get('/teacher/question-bank', [TeacherController::class, 'questionBankIndex'])->name('teacher.question-bank.index');
        Route::get('/teacher/quizzes', [TeacherController::class, 'quizzesIndex'])->name('teacher.quizzes.index');
        Route::get('/teacher/quizzes/create', [TeacherController::class, 'quizzesCreate'])->name('teacher.quizzes.create');
        Route::get('/teacher/assignments', [TeacherController::class, 'assignmentsIndex'])->name('teacher.assignments.index');
        Route::get('/teacher/assignments/create', [TeacherController::class, 'assignmentsCreate'])->name('teacher.assignments.create');
        Route::get('/teacher/assignments/{assignment}/submissions', [TeacherController::class, 'assignmentSubmissions'])->name('teacher.assignments.submissions');
        Route::get('/teacher/reports/performance', [TeacherController::class, 'performanceReportPage'])->name('teacher.reports.performance');
    });

    Route::middleware('role:student')->group(function () {
        Route::get('/student/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
        Route::get('/student/quizzes', [StudentController::class, 'quizzesIndex'])->name('student.quizzes.index');
        Route::get('/student/quizzes/{quiz}/attempt', [StudentController::class, 'quizAttemptForm'])->name('student.quizzes.attempt');
        Route::get('/student/assignments', [StudentController::class, 'assignmentsIndex'])->name('student.assignments.index');
        Route::get('/student/assignments/{assignment}', [StudentController::class, 'assignmentShow'])->name('student.assignments.show');
        Route::get('/student/results', [StudentController::class, 'myResults'])->name('student.results');
    });

    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/users/{user}/edit', [AdminController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminController::class, 'update'])->name('users.update');
        Route::post('/users/{user}/toggle-block', [AdminController::class, 'toggleBlock'])->name('users.toggle-block');
    });

    Route::middleware('role:admin')->prefix('admin/qams')->name('admin.qams.')->group(function () {
        Route::get('/', [AdminManagementController::class, 'hub'])->name('hub');
        Route::get('/classes', [AdminManagementController::class, 'classesIndex'])->name('classes.index');
        Route::post('/classes', [AdminManagementController::class, 'storeClass'])->name('classes.store');
        Route::put('/classes/{class}', [AdminManagementController::class, 'updateClass'])->name('classes.update');
        Route::get('/subjects', [AdminManagementController::class, 'subjectsIndex'])->name('subjects.index');
        Route::post('/subjects', [AdminManagementController::class, 'storeSubject'])->name('subjects.store');
        Route::put('/subjects/{subject}', [AdminManagementController::class, 'updateSubject'])->name('subjects.update');
        Route::get('/teachers', [AdminManagementController::class, 'teachersIndex'])->name('teachers.index');
        Route::get('/teachers/create', [AdminManagementController::class, 'teachersCreate'])->name('teachers.create');
        Route::post('/teachers', [AdminManagementController::class, 'registerTeacher'])->name('teachers.store');
        Route::get('/teachers/{teacher}/edit', [AdminManagementController::class, 'teachersEdit'])->name('teachers.edit');
        Route::put('/teachers/{teacher}', [AdminManagementController::class, 'updateTeacher'])->name('teachers.update');
        Route::get('/students', [AdminManagementController::class, 'studentsIndex'])->name('students.index');
        Route::get('/students/create', [AdminManagementController::class, 'studentsCreate'])->name('students.create');
        Route::post('/students', [AdminManagementController::class, 'registerStudent'])->name('students.store');
        Route::get('/students/{student}/edit', [AdminManagementController::class, 'studentsEdit'])->name('students.edit');
        Route::put('/students/{student}', [AdminManagementController::class, 'updateStudent'])->name('students.update');
        Route::post('/subjects/{subject}/assign-teacher', [AdminManagementController::class, 'assignTeacherToSubject'])->name('subjects.assign-teacher');
        Route::get('/reports', [AdminManagementController::class, 'reports'])->name('reports');
    });

    Route::middleware('role:teacher')->prefix('teacher/qams')->group(function () {
        Route::post('/question-bank', [TeacherController::class, 'storeQuestion'])->name('teacher.qams.question-bank.store');
        Route::post('/quizzes', [TeacherController::class, 'createQuiz'])->name('teacher.qams.quizzes.store');
        Route::post('/quizzes/{quiz}/extend', [TeacherController::class, 'extendQuiz'])->name('teacher.qams.quizzes.extend');
        Route::post('/quizzes/{quiz}/publish', [TeacherController::class, 'publishQuiz'])->name('teacher.qams.quizzes.publish');
        Route::post('/assignments', [TeacherController::class, 'createAssignment'])->name('teacher.qams.assignments.store');
        Route::post('/assignments/{assignment}/extend', [TeacherController::class, 'extendAssignment'])->name('teacher.qams.assignments.extend');
        Route::post('/assignments/{assignment}/publish', [TeacherController::class, 'publishAssignment'])->name('teacher.qams.assignments.publish');
        Route::post('/assignment-submissions/{submission}/grade', [TeacherController::class, 'gradeAssignment'])->name('teacher.qams.submissions.grade');
        Route::get('/reports/performance', [TeacherController::class, 'performanceReport'])->name('teacher.qams.reports.performance');
    });

    Route::middleware('role:student')->prefix('student/qams')->group(function () {
        Route::post('/quizzes/{quiz}/attempt', [StudentController::class, 'attemptQuiz'])->name('student.qams.quizzes.attempt');
        Route::post('/assignments/{assignment}/submit', [StudentController::class, 'submitAssignment'])->name('student.qams.assignments.submit');
        Route::get('/results', [StudentController::class, 'myResults'])->name('student.qams.results');
    });
});
