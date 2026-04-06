<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonOrRedirect;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuestionBankItem;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    use RespondsWithJsonOrRedirect;

    public function dashboard()
    {
        $subjectIds = auth()->user()->enrolledSubjects()->pluck('id');

        $upcomingQuizzes = Quiz::query()
            ->where('published', true)
            ->whereIn('subject_id', $subjectIds)
            ->where('deadline', '>=', now())
            ->with('subject')
            ->orderBy('deadline')
            ->take(5)
            ->get();

        $pendingAssignments = Assignment::query()
            ->where('published', true)
            ->whereIn('subject_id', $subjectIds)
            ->where('deadline', '>=', now())
            ->whereHas('submissions', function ($q) {
                $q->where('student_id', auth()->id())->where('status', 'pending');
            })
            ->with('subject')
            ->orderBy('deadline')
            ->take(5)
            ->get();

        return view('student.dashboard', compact('upcomingQuizzes', 'pendingAssignments'));
    }

    public function quizzesIndex()
    {
        $subjectIds = auth()->user()->enrolledSubjects()->pluck('id');
        $quizzes = Quiz::query()
            ->where('published', true)
            ->whereIn('subject_id', $subjectIds)
            ->with('subject.schoolClass')
            ->orderByDesc('deadline')
            ->paginate(15);
        $attempts = QuizAttempt::where('student_id', auth()->id())->get()->keyBy('quiz_id');

        return view('student.quizzes.index', compact('quizzes', 'attempts'));
    }

    public function quizAttemptForm(Quiz $quiz)
    {
        abort_unless($quiz->published, 403);
        abort_unless($quiz->subject->students()->whereKey(auth()->id())->exists(), 403);
        abort_if(Carbon::now()->greaterThan($quiz->deadline), 403);
        abort_if(QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', auth()->id())->exists(), 403);

        $questions = QuestionBankItem::where('subject_id', $quiz->subject_id)->orderBy('id')->get();
        $quiz->load('subject.schoolClass');

        return view('student.quizzes.attempt', compact('quiz', 'questions'));
    }

    public function assignmentsIndex()
    {
        $subjectIds = auth()->user()->enrolledSubjects()->pluck('id');
        $assignments = Assignment::query()
            ->where('published', true)
            ->whereIn('subject_id', $subjectIds)
            ->with('subject.schoolClass')
            ->orderByDesc('deadline')
            ->paginate(15);
        $submissions = AssignmentSubmission::where('student_id', auth()->id())->get()->keyBy('assignment_id');

        return view('student.assignments.index', compact('assignments', 'submissions'));
    }

    public function assignmentShow(Assignment $assignment)
    {
        abort_unless($assignment->published, 403);
        abort_unless($assignment->subject->students()->whereKey(auth()->id())->exists(), 403);

        $submission = AssignmentSubmission::firstOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => auth()->id()],
            ['status' => 'pending', 'marks' => 0]
        );
        $assignment->load('subject.schoolClass');

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    public function attemptQuiz(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->published, 403);
        abort_unless($quiz->subject->students()->whereKey(auth()->id())->exists(), 403);

        if (! $this->wantsApiResponse($request)) {
            $raw = $request->input('answers', []);
            $answers = [];
            foreach ($raw as $questionId => $selected) {
                if ($selected !== null && $selected !== '') {
                    $answers[] = [
                        'question_id' => (int) $questionId,
                        'selected_option' => strtoupper((string) $selected),
                    ];
                }
            }
            $request->merge(['answers' => $answers]);
        }

        $validated = $request->validate([
            'answers' => ['required', 'array', 'min:1'],
            'answers.*.question_id' => ['required', 'exists:question_bank_items,id'],
            'answers.*.selected_option' => ['required', 'in:A,B,C,D'],
        ]);

        abort_if(Carbon::now()->greaterThan($quiz->deadline), 422, 'Quiz deadline has passed.');

        return DB::transaction(function () use ($validated, $quiz, $request) {
            abort_if(QuizAttempt::where('quiz_id', $quiz->id)->where('student_id', auth()->id())->exists(), 422, 'Quiz already attempted.');

            $questionIds = collect($validated['answers'])->pluck('question_id')->unique()->values();
            $questions = QuestionBankItem::whereIn('id', $questionIds)
                ->where('subject_id', $quiz->subject_id)
                ->get()
                ->keyBy('id');

            abort_if($questions->count() !== $questionIds->count(), 422, 'Invalid questions for this quiz.');

            $score = 0;
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'student_id' => auth()->id(),
                'score' => 0,
                'total_questions' => $questionIds->count(),
                'submitted_at' => Carbon::now(),
            ]);

            foreach ($validated['answers'] as $answer) {
                $question = $questions[(int) $answer['question_id']];
                $isCorrect = $question->correct_option === $answer['selected_option'];
                if ($isCorrect) {
                    $score++;
                }

                QuizAnswer::create([
                    'quiz_attempt_id' => $attempt->id,
                    'question_bank_item_id' => $question->id,
                    'selected_option' => $answer['selected_option'],
                    'is_correct' => $isCorrect,
                ]);
            }

            $attempt->update(['score' => $score]);

            if ($this->wantsApiResponse($request)) {
                return response()->json(['attempt_id' => $attempt->id, 'score' => $score], 201);
            }

            return redirect()->route('student.quizzes.index')->with('success', "Quiz submitted. Score: {$score} / {$attempt->total_questions}.");
        });
    }

    public function submitAssignment(Request $request, Assignment $assignment)
    {
        abort_unless($assignment->published, 403);
        abort_unless($assignment->subject->students()->whereKey(auth()->id())->exists(), 403);

        $filePath = null;
        if ($request->hasFile('file')) {
            $request->validate(['file' => ['required', 'file', 'max:10240']]);
            $filePath = $request->file('file')->store('assignment_submissions', 'public');
        } else {
            $request->validate(['file_path' => ['required', 'string', 'max:255']]);
            $filePath = $request->input('file_path');
        }

        $isLate = Carbon::now()->greaterThan($assignment->deadline);

        $submission = AssignmentSubmission::updateOrCreate(
            ['assignment_id' => $assignment->id, 'student_id' => auth()->id()],
            [
                'file_path' => $filePath,
                'submitted_at' => Carbon::now(),
                'marks' => 0,
                'status' => $isLate ? 'auto_zero' : 'pending',
                'feedback' => $isLate ? 'Auto-zero: submitted after deadline.' : null,
            ]
        );

        if ($this->wantsApiResponse($request)) {
            return response()->json($submission, 201);
        }

        return redirect()->route('student.assignments.show', $assignment)->with('success', 'Assignment submitted.');
    }

    public function myResults(Request $request)
    {
        AssignmentSubmission::applyAutoZeroMarks();

        $studentId = auth()->id();

        $quizResults = QuizAttempt::with('quiz.subject')
            ->where('student_id', $studentId)
            ->orderByDesc('submitted_at')
            ->get();

        $assignmentResults = AssignmentSubmission::with('assignment.subject')
            ->where('student_id', $studentId)
            ->orderByDesc('updated_at')
            ->get();

        if ($this->wantsApiResponse($request)) {
            return response()->json([
                'quiz_results' => QuizAttempt::where('student_id', $studentId)
                    ->get(['quiz_id', 'score', 'total_questions', 'submitted_at']),
                'assignment_results' => AssignmentSubmission::where('student_id', $studentId)
                    ->get(['assignment_id', 'marks', 'status', 'feedback', 'submitted_at']),
            ]);
        }

        return view('student.results', compact('quizResults', 'assignmentResults'));
    }
}
