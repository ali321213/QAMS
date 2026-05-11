<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Concerns\RespondsWithJsonOrRedirect;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuestionBankItem;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    use RespondsWithJsonOrRedirect;

    public function dashboard()
    {
        $quizzesCount = Quiz::where('teacher_id', auth()->id())->count();
        $assignmentsCount = Assignment::where('teacher_id', auth()->id())->count();

        return view('teacher.dashboard', compact('quizzesCount', 'assignmentsCount'));
    }

    public function questionBankIndex()
    {
        $subjectIds = auth()->user()->assignedSubjects()->pluck('id');
        $questions = QuestionBankItem::whereIn('subject_id', $subjectIds)
            ->with('subject.schoolClass')
            ->orderByDesc('created_at')
            ->paginate(15);
        $subjects = auth()->user()->assignedSubjects()->with('schoolClass')->orderBy('name')->get();

        return view('teacher.question-bank.index', compact('questions', 'subjects'));
    }

    public function quizzesIndex()
    {
        $quizzes = Quiz::where('teacher_id', auth()->id())
            ->with('subject.schoolClass')
            ->orderByDesc('created_at')
            ->paginate(15);
        $subjects = auth()->user()->assignedSubjects()->with('schoolClass')->orderBy('name')->get();

        return view('teacher.quizzes.index', compact('quizzes', 'subjects'));
    }

    public function quizzesCreate()
    {
        $subjects = auth()->user()->assignedSubjects()->with('schoolClass')->orderBy('name')->get();

        return view('teacher.quizzes.create', compact('subjects'));
    }

    public function assignmentsIndex()
    {
        $assignments = Assignment::where('teacher_id', auth()->id())
            ->with('subject.schoolClass')
            ->orderByDesc('created_at')
            ->paginate(15);
        $subjects = auth()->user()->assignedSubjects()->with('schoolClass')->orderBy('name')->get();

        return view('teacher.assignments.index', compact('assignments', 'subjects'));
    }

    public function assignmentsCreate()
    {
        $subjects = auth()->user()->assignedSubjects()->with('schoolClass')->orderBy('name')->get();

        return view('teacher.assignments.create', compact('subjects'));
    }

    public function assignmentSubmissions(Assignment $assignment)
    {
        abort_unless($assignment->teacher_id === auth()->id(), 403);
        $assignment->load(['submissions.student', 'subject.schoolClass']);

        return view('teacher.assignments.submissions', compact('assignment'));
    }

    public function performanceReportPage(Request $request)
    {
        AssignmentSubmission::applyAutoZeroMarks();
        $subjects = auth()->user()->assignedSubjects()->with('schoolClass')->orderBy('name')->get();
        $subjectId = (int) $request->query('subject_id', $subjects->first()?->id ?? 0);
        $report = null;

        if ($subjectId && $subjects->contains('id', $subjectId)) {
            $subject = Subject::findOrFail($subjectId);
            $quizAverage = QuizAttempt::query()
                ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
                ->where('quizzes.subject_id', $subject->id)
                ->avg('quiz_attempts.score');
            $assignmentAverage = AssignmentSubmission::query()
                ->join('assignments', 'assignments.id', '=', 'assignment_submissions.assignment_id')
                ->where('assignments.subject_id', $subject->id)
                ->avg('assignment_submissions.marks');
            $report = [
                'subject' => $subject,
                'quiz_average' => round((float) $quizAverage, 2),
                'assignment_average' => round((float) $assignmentAverage, 2),
                'generated_at' => Carbon::now(),
            ];
        }

        return view('teacher.reports.performance', compact('subjects', 'subjectId', 'report'));
    }

    public function storeQuestion(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'question' => ['required', 'string'],
            'option_a' => ['required', 'string'],
            'option_b' => ['required', 'string'],
            'option_c' => ['required', 'string'],
            'option_d' => ['required', 'string'],
            'correct_option' => ['required', 'in:A,B,C,D'],
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);
        abort_unless($subject->teachers()->whereKey(auth()->id())->exists(), 403);

        $question = QuestionBankItem::create(array_merge($validated, ['teacher_id' => auth()->id()]));

        if ($this->wantsApiResponse($request)) {
            return response()->json($question, 201);
        }

        return redirect()->route('teacher.question-bank.index')->with('success', 'Question added to bank.');
    }

    public function createQuiz(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'deadline' => ['required', 'date', 'after:starts_at'],
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);
        abort_unless($subject->teachers()->whereKey(auth()->id())->exists(), 403);

        $quiz = Quiz::create(array_merge($validated, ['teacher_id' => auth()->id(), 'published' => false]));

        if ($this->wantsApiResponse($request)) {
            return response()->json($quiz, 201);
        }

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz created. Publish when ready.');
    }

    public function extendQuiz(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->teacher_id === auth()->id(), 403);
        $validated = $request->validate(['deadline' => ['required', 'date', 'after:'.$quiz->starts_at->toDateTimeString()]]);
        $quiz->update(['deadline' => $validated['deadline']]);

        if ($this->wantsApiResponse($request)) {
            return response()->json($quiz);
        }

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz deadline updated.');
    }

    public function publishQuiz(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->teacher_id === auth()->id(), 403);
        $quiz->update(['published' => true]);

        if ($this->wantsApiResponse($request)) {
            return response()->json(['message' => 'Quiz published.']);
        }

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz published to students.');
    }

    public function createAssignment(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'deadline' => ['required', 'date', 'after_or_equal:now'],
        ]);

        $subject = Subject::findOrFail($validated['subject_id']);
        abort_unless($subject->teachers()->whereKey(auth()->id())->exists(), 403);

        $assignment = Assignment::create(array_merge($validated, ['teacher_id' => auth()->id(), 'published' => false]));

        foreach ($subject->students()->pluck('id') as $studentId) {
            AssignmentSubmission::firstOrCreate(
                ['assignment_id' => $assignment->id, 'student_id' => $studentId],
                ['status' => 'pending', 'marks' => 0]
            );
        }

        if ($this->wantsApiResponse($request)) {
            return response()->json($assignment, 201);
        }

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment created. Publish when ready.');
    }

    public function extendAssignment(Request $request, Assignment $assignment)
    {
        abort_unless($assignment->teacher_id === auth()->id(), 403);
        $validated = $request->validate(['deadline' => ['required', 'date', 'after_or_equal:now']]);
        $assignment->update(['deadline' => $validated['deadline']]);

        if ($this->wantsApiResponse($request)) {
            return response()->json($assignment);
        }

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment deadline updated.');
    }

    public function publishAssignment(Request $request, Assignment $assignment)
    {
        abort_unless($assignment->teacher_id === auth()->id(), 403);
        $assignment->update(['published' => true]);

        if ($this->wantsApiResponse($request)) {
            return response()->json(['message' => 'Assignment published.']);
        }

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment published to students.');
    }

    public function gradeAssignment(Request $request, AssignmentSubmission $submission)
    {
        abort_unless($submission->assignment->teacher_id === auth()->id(), 403);
        $validated = $request->validate([
            'marks' => ['required', 'integer', 'min:0', 'max:100'],
            'feedback' => ['nullable', 'string'],
        ]);

        $submission->update([
            'marks' => $validated['marks'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_by' => auth()->id(),
            'status' => 'graded',
        ]);

        if ($this->wantsApiResponse($request)) {
            return response()->json($submission);
        }

        return redirect()
            ->route('teacher.assignments.submissions', $submission->assignment_id)
            ->with('success', 'Submission graded.');
    }

    public function performanceReport(Request $request)
    {
        AssignmentSubmission::applyAutoZeroMarks();

        $validated = $request->validate(['subject_id' => ['required', 'exists:subjects,id']]);
        $subject = Subject::findOrFail($validated['subject_id']);
        abort_unless($subject->teachers()->whereKey(auth()->id())->exists(), 403);

        $quizAverage = QuizAttempt::query()
            ->join('quizzes', 'quizzes.id', '=', 'quiz_attempts.quiz_id')
            ->where('quizzes.subject_id', $subject->id)
            ->avg('quiz_attempts.score');

        $assignmentAverage = AssignmentSubmission::query()
            ->join('assignments', 'assignments.id', '=', 'assignment_submissions.assignment_id')
            ->where('assignments.subject_id', $subject->id)
            ->avg('assignment_submissions.marks');

        $payload = [
            'subject_id' => $subject->id,
            'generated_at' => Carbon::now()->toDateTimeString(),
            'quiz_average' => round((float) $quizAverage, 2),
            'assignment_average' => round((float) $assignmentAverage, 2),
        ];

        if ($this->wantsApiResponse($request)) {
            return response()->json($payload);
        }

        return redirect()->route('teacher.reports.performance', ['subject_id' => $subject->id]);
    }
}
