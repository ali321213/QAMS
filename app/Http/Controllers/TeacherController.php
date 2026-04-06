<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
<<<<<<< Updated upstream
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    public function dashboard()
    {
        $teacher = Auth::user()->load('teacher');
        $quizzesCount = Quiz::where('teacher_id', $teacher->teacher->id ?? null)->count();
        $assignmentsCount = Assignment::where('teacher_id', $teacher->teacher->id ?? null)->count();

        return view('teacher.dashboard', compact('teacher', 'quizzesCount', 'assignmentsCount'));
    }

    public function listQuizzes()
    {
        $teacher = Auth::user()->teacher;
        $quizzes = Quiz::with('subject')
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('teacher.quizzes.index', compact('quizzes'));
    }

    public function createQuiz()
    {
        $teacher = Auth::user()->teacher;
        $subjects = $teacher->subjects()->with('class')->get();

        return view('teacher.quizzes.create', compact('subjects'));
    }

    public function storeQuiz(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.question_text' => ['required', 'string'],
            'questions.*.option_a' => ['required', 'string'],
            'questions.*.option_b' => ['required', 'string'],
            'questions.*.option_c' => ['nullable', 'string'],
            'questions.*.option_d' => ['nullable', 'string'],
            'questions.*.correct_option' => ['required', 'in:a,b,c,d'],
            'questions.*.marks' => ['required', 'integer', 'min:1'],
        ]);

        $quiz = Quiz::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $teacher->id,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'],
            'is_published' => false,
        ]);

        foreach ($validated['questions'] as $q) {
            QuizQuestion::create([
                'quiz_id' => $quiz->id,
                'question_text' => $q['question_text'],
                'option_a' => $q['option_a'],
                'option_b' => $q['option_b'],
                'option_c' => $q['option_c'] ?? null,
                'option_d' => $q['option_d'] ?? null,
                'correct_option' => $q['correct_option'],
                'marks' => $q['marks'],
            ]);
        }

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz created successfully.');
    }

    public function editQuiz(Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);
        $teacher = Auth::user()->teacher;
        $subjects = $teacher->subjects()->with('class')->get();
        $quiz->load('questions');

        return view('teacher.quizzes.edit', compact('quiz', 'subjects'));
    }

    public function updateQuiz(Request $request, Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
        ]);

        $quiz->update($validated);

        return redirect()->route('teacher.quizzes.index')->with('success', 'Quiz updated successfully.');
=======
use App\Models\QuestionBankItem;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
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

        return response()->json($question, 201);
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

        return response()->json($quiz, 201);
    }

    public function extendQuiz(Request $request, Quiz $quiz)
    {
        abort_unless($quiz->teacher_id === auth()->id(), 403);
        $validated = $request->validate(['deadline' => ['required', 'date', 'after:'.$quiz->starts_at->toDateTimeString()]]);
        $quiz->update(['deadline' => $validated['deadline']]);

        return response()->json($quiz);
>>>>>>> Stashed changes
    }

    public function publishQuiz(Quiz $quiz)
    {
<<<<<<< Updated upstream
        $this->authorizeQuiz($quiz);

        $quiz->is_published = true;
        $quiz->save();

        return back()->with('success', 'Quiz published for students.');
    }

    public function quizResults(Quiz $quiz)
    {
        $this->authorizeQuiz($quiz);

        $quiz->load(['attempts.student.user']);

        return view('teacher.quizzes.results', compact('quiz'));
    }

    public function listAssignments()
    {
        $teacher = Auth::user()->teacher;
        $assignments = Assignment::with('subject')
            ->where('teacher_id', $teacher->id)
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('teacher.assignments.index', compact('assignments'));
    }

    public function createAssignment()
    {
        $teacher = Auth::user()->teacher;
        $subjects = $teacher->subjects()->with('class')->get();

        return view('teacher.assignments.create', compact('subjects'));
    }

    public function storeAssignment(Request $request)
    {
        $teacher = Auth::user()->teacher;

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'deadline_at' => ['required', 'date', 'after:now'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('assignment_files', 'public');
        }

        Assignment::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'attachment_path' => $attachmentPath,
            'subject_id' => $validated['subject_id'],
            'teacher_id' => $teacher->id,
            'deadline_at' => $validated['deadline_at'],
            'assigned_at' => now(),
            'is_closed' => false,
        ]);

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment created successfully.');
    }

    public function editAssignment(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);
        $teacher = Auth::user()->teacher;
        $subjects = $teacher->subjects()->with('class')->get();

        return view('teacher.assignments.edit', compact('assignment', 'subjects'));
    }

    public function updateAssignment(Request $request, Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'deadline_at' => ['required', 'date'],
            'attachment' => ['nullable', 'file', 'max:10240'],
        ]);

        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('assignment_files', 'public');
            $validated['attachment_path'] = $path;
        }

        $assignment->update($validated);

        return redirect()->route('teacher.assignments.index')->with('success', 'Assignment updated successfully.');
    }

    public function extendAssignmentDeadline(Request $request, Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $validated = $request->validate([
            'extended_deadline_at' => ['required', 'date', 'after:deadline_at'],
        ]);

        $assignment->extended_deadline_at = $validated['extended_deadline_at'];
        $assignment->save();

        return back()->with('success', 'Assignment deadline extended.');
    }

    public function viewAssignmentSubmissions(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $assignment->load(['submissions.student.user']);

        return view('teacher.assignments.submissions', compact('assignment'));
    }

    public function gradeAssignmentSubmission(Request $request, Assignment $assignment, AssignmentSubmission $submission)
    {
        $this->authorizeAssignment($assignment);

        $validated = $request->validate([
            'marks' => ['required', 'integer', 'min:0'],
            'feedback' => ['nullable', 'string'],
        ]);

        $submission->marks = $validated['marks'];
        $submission->feedback = $validated['feedback'] ?? null;
        $submission->status = 'graded';
        $submission->save();

        return back()->with('success', 'Submission graded successfully.');
    }

    public function autoZeroMissingSubmissions(Assignment $assignment)
    {
        $this->authorizeAssignment($assignment);

        $effectiveDeadline = $assignment->effectiveDeadline();
        if (now()->lessThan($effectiveDeadline)) {
            return back()->withErrors(['error' => 'You can only auto-assign zero marks after the deadline has passed.']);
        }

        $submittedStudentIds = $assignment->submissions()->pluck('student_id')->all();

        $students = Student::where('class_id', $assignment->subject->class_id)->get();

        foreach ($students as $student) {
            if (! in_array($student->id, $submittedStudentIds, true)) {
                AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $student->id,
                    'file_path' => null,
                    'submitted_at' => null,
                    'marks' => 0,
                    'feedback' => 'Not submitted on time. Auto-assigned zero.',
                    'status' => 'auto_zero',
                ]);
            }
        }

        return back()->with('success', 'Zero marks assigned to all students who did not submit on time.');
    }

    protected function authorizeQuiz(Quiz $quiz): void
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $quiz->teacher_id === $teacher->id, 403);
    }

    protected function authorizeAssignment(Assignment $assignment): void
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $assignment->teacher_id === $teacher->id, 403);
    }
}

=======
        abort_unless($quiz->teacher_id === auth()->id(), 403);
        $quiz->update(['published' => true]);

        return response()->json(['message' => 'Quiz published.']);
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

        // create placeholders for auto-zero tracking for every enrolled student
        foreach ($subject->students()->pluck('users.id') as $studentId) {
            AssignmentSubmission::firstOrCreate(
                ['assignment_id' => $assignment->id, 'student_id' => $studentId],
                ['status' => 'pending', 'marks' => 0]
            );
        }

        return response()->json($assignment, 201);
    }

    public function extendAssignment(Request $request, Assignment $assignment)
    {
        abort_unless($assignment->teacher_id === auth()->id(), 403);
        $validated = $request->validate(['deadline' => ['required', 'date', 'after_or_equal:now']]);
        $assignment->update(['deadline' => $validated['deadline']]);

        return response()->json($assignment);
    }

    public function publishAssignment(Assignment $assignment)
    {
        abort_unless($assignment->teacher_id === auth()->id(), 403);
        $assignment->update(['published' => true]);

        return response()->json(['message' => 'Assignment published.']);
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

        return response()->json($submission);
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

        return response()->json([
            'subject_id' => $subject->id,
            'generated_at' => Carbon::now()->toDateTimeString(),
            'quiz_average' => round((float) $quizAverage, 2),
            'assignment_average' => round((float) $assignmentAverage, 2),
        ]);
    }
}
>>>>>>> Stashed changes
