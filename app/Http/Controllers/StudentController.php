<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentController extends Controller
{
    public function dashboard()
    {
        $student = Auth::user()->load('student');

        $upcomingQuizzes = Quiz::where('is_published', true)
            ->whereHas('subject', function ($q) use ($student) {
                $q->where('class_id', $student->student->class_id ?? null);
            })
            ->where('ends_at', '>=', now())
            ->orderBy('starts_at')
            ->take(5)
            ->get();

        $pendingAssignments = Assignment::whereHas('subject', function ($q) use ($student) {
            $q->where('class_id', $student->student->class_id ?? null);
        })
            ->where(function ($q) {
                $q->whereNull('extended_deadline_at')
                    ->where('deadline_at', '>=', now())
                    ->orWhere('extended_deadline_at', '>=', now());
            })
            ->orderBy('deadline_at')
            ->take(5)
            ->get();

        return view('student.dashboard', compact('student', 'upcomingQuizzes', 'pendingAssignments'));
    }

    public function listQuizzes()
    {
        $student = Auth::user()->student;

        $quizzes = Quiz::with('subject')
            ->where('is_published', true)
            ->whereHas('subject', function ($q) use ($student) {
                $q->where('class_id', $student->class_id);
            })
            ->orderBy('starts_at')
            ->paginate(10);

        $attempts = QuizAttempt::where('student_id', $student->id)->get()->keyBy('quiz_id');

        return view('student.quizzes.index', compact('quizzes', 'attempts'));
    }

    public function showQuiz(Quiz $quiz)
    {
        // Backwards compatibility: redirect to first question view
        return redirect()->route('student.quizzes.question.show', ['quiz' => $quiz->id, 'index' => 0]);
    }

    public function startQuiz(Quiz $quiz)
    {
        $student = Auth::user()->student;
        $this->ensureQuizAccessible($quiz, $student->class_id);

        QuizAttempt::firstOrCreate(
            ['quiz_id' => $quiz->id, 'student_id' => $student->id],
            ['started_at' => now(), 'status' => 'in_progress']
        );

        return redirect()->route('student.quizzes.question.show', ['quiz' => $quiz->id, 'index' => 0]);
    }

    public function showQuizQuestion(Quiz $quiz, int $index)
    {
        $student = Auth::user()->student;
        $this->ensureQuizAccessible($quiz, $student->class_id);

        $questions = $quiz->questions()->orderBy('id')->get();
        $totalQuestions = $questions->count();

        abort_if($totalQuestions === 0 || $index < 0 || $index >= $totalQuestions, 404);

        $question = $questions[$index];

        $attempt = QuizAttempt::firstOrCreate(
            ['quiz_id' => $quiz->id, 'student_id' => $student->id],
            ['started_at' => now(), 'status' => 'in_progress']
        );

        if ($attempt->status === 'submitted') {
            return redirect()->route('student.quizzes.index')
                ->withErrors(['error' => 'You have already submitted this quiz.']);
        }

        return view('student.quizzes.show', [
            'quiz' => $quiz,
            'question' => $question,
            'attempt' => $attempt,
            'index' => $index,
            'totalQuestions' => $totalQuestions,
            'secondsPerQuestion' => 60,
        ]);
    }

    public function answerQuizQuestion(Request $request, Quiz $quiz, int $index)
    {
        $student = Auth::user()->student;
        $this->ensureQuizAccessible($quiz, $student->class_id);

        $questions = $quiz->questions()->orderBy('id')->get();
        $totalQuestions = $questions->count();

        abort_if($totalQuestions === 0 || $index < 0 || $index >= $totalQuestions, 404);

        $question = $questions[$index];

        $attempt = QuizAttempt::firstOrCreate(
            ['quiz_id' => $quiz->id, 'student_id' => $student->id],
            ['started_at' => now(), 'status' => 'in_progress']
        );

        if ($attempt->status === 'submitted') {
            return redirect()->route('student.quizzes.index')
                ->withErrors(['error' => 'You have already submitted this quiz.']);
        }

        $selected = $request->input('selected_option');
        if (! in_array($selected, ['a', 'b', 'c', 'd'], true)) {
            $selected = null;
        }

        $isCorrect = $selected !== null && $selected === $question->correct_option;
        $earned = $isCorrect ? $question->marks : 0;

        QuizAnswer::updateOrCreate(
            [
                'quiz_attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'selected_option' => $selected,
                'is_correct' => $isCorrect,
                'earned_marks' => $earned,
            ]
        );

        // Recalculate total score from all answered questions
        $totalScore = QuizAnswer::where('quiz_attempt_id', $attempt->id)->sum('earned_marks');
        $attempt->total_score = $totalScore;

        $nextIndex = $index + 1;

        if ($nextIndex >= $totalQuestions) {
            $attempt->submitted_at = now();
            $attempt->status = 'submitted';
            $attempt->save();

            return redirect()->route('student.quizzes.index')
                ->with('success', 'Quiz submitted. Your score: '.$totalScore);
        }

        $attempt->save();

        return redirect()->route('student.quizzes.question.show', ['quiz' => $quiz->id, 'index' => $nextIndex]);
    }

    public function listAssignments()
    {
        $student = Auth::user()->student;

        $assignments = Assignment::with('subject')
            ->whereHas('subject', function ($q) use ($student) {
                $q->where('class_id', $student->class_id);
            })
            ->orderByDesc('assigned_at')
            ->paginate(10);

        $submissions = AssignmentSubmission::where('student_id', $student->id)->get()->keyBy('assignment_id');

        return view('student.assignments.index', compact('assignments', 'submissions'));
    }

    public function showAssignment(Assignment $assignment)
    {
        $student = Auth::user()->student;
        $this->ensureAssignmentAccessible($assignment, $student->class_id);

        $submission = AssignmentSubmission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    public function submitAssignment(Request $request, Assignment $assignment)
    {
        $student = Auth::user()->student;
        $this->ensureAssignmentAccessible($assignment, $student->class_id);

        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $filePath = $request->file('file')->store('assignments', 'public');

        $submission = AssignmentSubmission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => $student->id,
            ],
            [
                'file_path' => $filePath,
                'submitted_at' => now(),
                'status' => 'submitted',
            ]
        );

        return redirect()->route('student.assignments.show', $assignment)->with('success', 'Assignment submitted successfully.');
    }

    public function performanceReport()
    {
        $student = Auth::user()->student;

        $quizAttempts = QuizAttempt::with('quiz.subject')
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        $assignmentSubmissions = AssignmentSubmission::with('assignment.subject')
            ->where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->get();

        return view('student.reports.performance', compact('student', 'quizAttempts', 'assignmentSubmissions'));
    }

    protected function ensureQuizAccessible(Quiz $quiz, int $classId): void
    {
        abort_unless(
            $quiz->is_published &&
            $quiz->subject &&
            (int) $quiz->subject->class_id === $classId &&
            $quiz->ends_at >= now(),
            403
        );
    }

    protected function ensureAssignmentAccessible(Assignment $assignment, int $classId): void
    {
        abort_unless(
            $assignment->subject &&
            (int) $assignment->subject->class_id === $classId,
            403
        );
    }
}

