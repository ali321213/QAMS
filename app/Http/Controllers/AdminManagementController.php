<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminManagementController extends Controller
{
    public function storeClass(Request $request)
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:school_classes,name']]);
        $class = SchoolClass::create($validated);

        return response()->json($class, 201);
    }

    public function updateClass(Request $request, SchoolClass $class)
    {
        $validated = $request->validate(['name' => ['required', 'string', 'max:100', 'unique:school_classes,name,'.$class->id]]);
        $class->update($validated);

        return response()->json($class);
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'name' => ['required', 'string', 'max:100'],
        ]);
        $subject = Subject::create($validated);

        return response()->json($subject, 201);
    }

    public function updateSubject(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'name' => ['required', 'string', 'max:100'],
        ]);
        $subject->update($validated);

        return response()->json($subject);
    }

    public function registerStudent(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'user_name' => ['required', 'string', 'max:30', 'unique:users,user_name'],
            'password' => ['required', 'string', 'min:6'],
            'admission_number' => ['required', 'string', 'max:100', 'unique:student_profiles,admission_number'],
            'father_name' => ['required', 'string', 'max:100'],
            'picture_path' => ['nullable', 'string', 'max:255'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_ids' => ['array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ]);

        return DB::transaction(function () use ($validated) {
            $student = User::create([
                'name' => $validated['name'],
                'user_name' => $validated['user_name'],
                'password' => Hash::make($validated['password']),
                'role' => 'student',
                'active' => '1',
            ]);

            StudentProfile::create([
                'user_id' => $student->id,
                'admission_number' => $validated['admission_number'],
                'father_name' => $validated['father_name'],
                'picture_path' => $validated['picture_path'] ?? null,
                'school_class_id' => $validated['school_class_id'],
            ]);

            $student->enrolledSubjects()->sync($validated['subject_ids'] ?? []);

            return response()->json($student->load(['studentProfile', 'enrolledSubjects']), 201);
        });
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

        return DB::transaction(function () use ($validated) {
            $teacher = User::create([
                'name' => $validated['name'],
                'user_name' => $validated['user_name'],
                'password' => Hash::make($validated['password']),
                'role' => 'teacher',
                'active' => '1',
            ]);

            TeacherProfile::create([
                'user_id' => $teacher->id,
                'job_history' => $validated['job_history'] ?? null,
                'education' => $validated['education'] ?? null,
            ]);

            return response()->json($teacher->load('teacherProfile'), 201);
        });
    }

    public function updateStudent(Request $request, User $student)
    {
        abort_unless($student->isStudent(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'user_name' => ['required', 'string', 'max:30', 'unique:users,user_name,'.$student->id],
            'admission_number' => ['required', 'string', 'max:100', 'unique:student_profiles,admission_number,'.$student->studentProfile->id],
            'father_name' => ['required', 'string', 'max:100'],
            'picture_path' => ['nullable', 'string', 'max:255'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'subject_ids' => ['array'],
            'subject_ids.*' => ['exists:subjects,id'],
        ]);

        return DB::transaction(function () use ($validated, $student) {
            $student->update([
                'name' => $validated['name'],
                'user_name' => $validated['user_name'],
            ]);

            $student->studentProfile()->update([
                'admission_number' => $validated['admission_number'],
                'father_name' => $validated['father_name'],
                'picture_path' => $validated['picture_path'] ?? null,
                'school_class_id' => $validated['school_class_id'],
            ]);

            $student->enrolledSubjects()->sync($validated['subject_ids'] ?? []);

            return response()->json($student->load(['studentProfile', 'enrolledSubjects']));
        });
    }

    public function updateTeacher(Request $request, User $teacher)
    {
        abort_unless($teacher->isTeacher(), 404);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:30'],
            'user_name' => ['required', 'string', 'max:30', 'unique:users,user_name,'.$teacher->id],
            'job_history' => ['nullable', 'string'],
            'education' => ['nullable', 'string'],
        ]);

        $teacher->update(['name' => $validated['name'], 'user_name' => $validated['user_name']]);
        $teacher->teacherProfile()->update([
            'job_history' => $validated['job_history'] ?? null,
            'education' => $validated['education'] ?? null,
        ]);

        return response()->json($teacher->load('teacherProfile'));
    }

    public function assignTeacherToSubject(Request $request, Subject $subject)
    {
        $validated = $request->validate(['teacher_id' => ['required', 'exists:users,id']]);
        $teacher = User::findOrFail($validated['teacher_id']);
        abort_unless($teacher->isTeacher(), 422, 'Selected user is not a teacher.');

        $subject->teachers()->syncWithoutDetaching([$teacher->id]);

        return response()->json(['message' => 'Teacher assigned successfully.']);
    }

    public function reports()
    {
        return response()->json([
            'students_count' => User::where('role', 'student')->count(),
            'teachers_count' => User::where('role', 'teacher')->count(),
            'subjects_count' => Subject::count(),
            'classes_count' => SchoolClass::count(),
        ]);
    }
}
