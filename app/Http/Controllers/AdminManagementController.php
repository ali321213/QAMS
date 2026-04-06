<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\RespondsWithJsonOrRedirect;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminManagementController extends Controller
{
    use RespondsWithJsonOrRedirect;

    public function hub()
    {
        return view('admin.qams.hub');
    }

    public function classesIndex()
    {
        $classes = SchoolClass::withCount('subjects')->orderBy('name')->get();

        return view('admin.qams.classes', compact('classes'));
    }

    public function subjectsIndex()
    {
        $subjects = Subject::with(['schoolClass', 'teachers'])->orderBy('school_class_id')->orderBy('name')->get();
        $classes = SchoolClass::orderBy('name')->get();
        $teachers = User::where('role', 'teacher')->orderBy('name')->get();

        return view('admin.qams.subjects', compact('subjects', 'classes', 'teachers'));
    }

    public function teachersIndex()
    {
        $teachers = User::where('role', 'teacher')->with('teacherProfile')->withCount('assignedSubjects')->orderBy('name')->paginate(15);

        return view('admin.qams.teachers.index', compact('teachers'));
    }

    public function teachersCreate()
    {
        return view('admin.qams.teachers.create');
    }

    public function teachersEdit(User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404);
        $teacher->load('teacherProfile');

        return view('admin.qams.teachers.edit', compact('teacher'));
    }

    public function studentsIndex()
    {
        $students = User::where('role', 'student')->with(['studentProfile.schoolClass', 'enrolledSubjects'])->orderBy('name')->paginate(15);

        return view('admin.qams.students.index', compact('students'));
    }

    public function studentsCreate()
    {
        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::with('schoolClass')->orderBy('name')->get();

        return view('admin.qams.students.create', compact('classes', 'subjects'));
    }

    public function studentsEdit(User $student)
    {
        abort_unless($student->isStudent(), 404);
        $student->load(['studentProfile', 'enrolledSubjects']);
        $classes = SchoolClass::orderBy('name')->get();
        $subjects = Subject::with('schoolClass')->orderBy('name')->get();

        return view('admin.qams.students.edit', compact('student', 'classes', 'subjects'));
    }

    public function storeClass(Request $request)
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:school_classes,name']]);
        $class = SchoolClass::create($validated);

        if ($this->wantsApiResponse($request)) {
            return response()->json($class, 201);
        }

        return redirect()->route('admin.qams.classes.index')->with('success', 'Class created.');
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:school_classes,name,'.$class->id]]);
        $class->update($validated);

        if ($this->wantsApiResponse($request)) {
            return response()->json($class);
        }

        return redirect()->route('admin.qams.classes.index')->with('success', 'Class updated.');
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'name' => ['required', 'string', 'max:100'],
        ]);
        $subject = Subject::create($validated);

        if ($this->wantsApiResponse($request)) {
            return response()->json($subject, 201);
        }

        return redirect()->route('admin.qams.subjects.index')->with('success', 'Subject created.');
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'name' => ['required', 'string', 'max:100'],
        ]);
        $subject->update($validated);

        if ($this->wantsApiResponse($request)) {
            return response()->json($subject);
        }

        return redirect()->route('admin.qams.subjects.index')->with('success', 'Subject updated.');
    }

    public function registerStudent(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'user_name' => ['required', 'string', 'max:30', 'unique:users,user_name'],
            'password' => ['required', 'string', 'min:6'],
            'admission_number' => ['required', 'string', 'max:100', 'unique:student_profiles,admission_number'],
            'father_name' => ['required', 'string', 'max:100'],
            'picture' => ['nullable', 'image', 'max:2048'],
            'picture_path' => ['nullable', 'string', 'max:255'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_ids' => ['array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ]);

        if ($request->hasFile('picture')) {
            $validated['picture_path'] = $request->file('picture')->store('student_photos', 'public');
        }

        $student = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'user_name' => $validated['user_name'],
                'password' => Hash::make($validated['password']),
                'role' => 'student',
                'active' => '1',
            ]);

            StudentProfile::create([
                'user_id' => $user->id,
                'admission_number' => $validated['admission_number'],
                'father_name' => $validated['father_name'],
                'picture_path' => $validated['picture_path'] ?? null,
                'school_class_id' => $validated['school_class_id'],
            ]);

            $user->enrolledSubjects()->sync($validated['subject_ids'] ?? []);

            return $user->load(['studentProfile', 'enrolledSubjects']);
        });

        if ($this->wantsApiResponse($request)) {
            return response()->json($student, 201);
        }

        return redirect()->route('admin.qams.students.index')->with('success', 'Student registered.');
    }

    public function registerTeacher(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'user_name' => ['required', 'string', 'max:30', 'unique:users,user_name'],
            'password' => ['required', 'string', 'min:6'],
            'job_history' => ['nullable', 'string'],
            'education' => ['nullable', 'string'],
        ]);

        $teacher = DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'user_name' => $validated['user_name'],
                'password' => Hash::make($validated['password']),
                'role' => 'teacher',
                'active' => '1',
            ]);

            TeacherProfile::create([
                'user_id' => $user->id,
                'job_history' => $validated['job_history'] ?? null,
                'education' => $validated['education'] ?? null,
            ]);

            return $user->load('teacherProfile');
        });

        if ($this->wantsApiResponse($request)) {
            return response()->json($teacher, 201);
        }

        return redirect()->route('admin.qams.teachers.index')->with('success', 'Teacher registered.');
    }

    public function updateStudent(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404);
        $student->load('studentProfile');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'user_name' => ['required', 'string', 'max:30', 'unique:users,user_name,'.$student->id],
            'password' => ['nullable', 'string', 'min:6'],
            'admission_number' => ['required', 'string', 'max:100', 'unique:student_profiles,admission_number,'.$student->studentProfile->id],
            'father_name' => ['required', 'string', 'max:100'],
            'picture' => ['nullable', 'image', 'max:2048'],
            'picture_path' => ['nullable', 'string', 'max:255'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_ids' => ['array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ]);

        if ($request->hasFile('picture')) {
            if ($student->studentProfile->picture_path) {
                Storage::disk('public')->delete($student->studentProfile->picture_path);
            }
            $validated['picture_path'] = $request->file('picture')->store('student_photos', 'public');
        }

        DB::transaction(function () use ($validated, $student) {
            $update = [
                'name' => $validated['name'],
                'user_name' => $validated['user_name'],
            ];
            if (! empty($validated['password'])) {
                $update['password'] = Hash::make($validated['password']);
            }
            $student->update($update);

            $profileData = [
                'admission_number' => $validated['admission_number'],
                'father_name' => $validated['father_name'],
                'school_class_id' => $validated['school_class_id'],
            ];
            if (array_key_exists('picture_path', $validated)) {
                $profileData['picture_path'] = $validated['picture_path'];
            }
            $student->studentProfile()->update($profileData);

            $student->enrolledSubjects()->sync($validated['subject_ids'] ?? []);
        });

        $student->refresh()->load(['studentProfile', 'enrolledSubjects']);

        if ($this->wantsApiResponse($request)) {
            return response()->json($student);
        }

        return redirect()->route('admin.qams.students.index')->with('success', 'Student updated.');
    }

    public function updateTeacher(Request $request, User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'user_name' => ['required', 'string', 'max:30', 'unique:users,user_name,'.$teacher->id],
            'password' => ['nullable', 'string', 'min:6'],
            'job_history' => ['nullable', 'string'],
            'education' => ['nullable', 'string'],
        ]);

        $update = ['name' => $validated['name'], 'user_name' => $validated['user_name']];
        if (! empty($validated['password'])) {
            $update['password'] = Hash::make($validated['password']);
        }
        $teacher->update($update);

        $teacher->teacherProfile()->updateOrCreate(
            ['user_id' => $teacher->id],
            [
                'job_history' => $validated['job_history'] ?? null,
                'education' => $validated['education'] ?? null,
            ]
        );

        $teacher->load('teacherProfile');

        if ($this->wantsApiResponse($request)) {
            return response()->json($teacher);
        }

        return redirect()->route('admin.qams.teachers.index')->with('success', 'Teacher updated.');
    }

    public function assignTeacherToSubject(Request $request, Subject $subject)
    {
        $validated = $request->validate(['teacher_id' => ['required', 'exists:users,id']]);
        $teacher = User::findOrFail($validated['teacher_id']);
        if (! $teacher->isTeacher()) {
            if ($this->wantsApiResponse($request)) {
                abort(422, 'Selected user is not a teacher.');
            }

            return back()->withErrors(['teacher_id' => 'Selected user is not a teacher.']);
        }

        $subject->teachers()->syncWithoutDetaching([$teacher->id]);

        if ($this->wantsApiResponse($request)) {
            return response()->json(['message' => 'Teacher assigned successfully.']);
        }

        return redirect()->route('admin.qams.subjects.index')->with('success', 'Teacher assigned to subject.');
    }

    public function reports(Request $request)
    {
        $stats = [
            'students_count' => User::where('role', 'student')->count(),
            'teachers_count' => User::where('role', 'teacher')->count(),
            'subjects_count' => Subject::count(),
            'classes_count' => SchoolClass::count(),
        ];

        if ($this->wantsApiResponse($request)) {
            return response()->json($stats);
        }

        return view('admin.qams.reports', compact('stats'));
    }
}
