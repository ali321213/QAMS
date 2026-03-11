<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
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
    }

    public function publishQuiz(Quiz $quiz)
    {
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

