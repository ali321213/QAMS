<?php

namespace Database\Seeders;
use App\Models\QuestionBankItem;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'user_name' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'active' => '1',
        ]);

        $teacher = User::create([
            'name' => 'Teacher One',
            'user_name' => 'teacher1',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'active' => '1',
        ]);

        $student = User::create([
            'name' => 'Student One',
            'user_name' => 'student1',
            'password' => Hash::make('password'),
            'role' => 'student',
            'active' => '1',
        ]);

        TeacherProfile::create([
            'user_id' => $teacher->id,
            'job_history' => '5 years teaching experience',
            'education' => 'MSc Mathematics',
        ]);

        $class = SchoolClass::create(['name' => 'Class 10']);
        $subject = Subject::create([
            'name' => 'Mathematics',
            'school_class_id' => $class->id,
        ]);

        StudentProfile::create([
            'user_id' => $student->id,
            'admission_number' => 'ADM-001',
            'father_name' => 'Father One',
            'picture_path' => null,
            'school_class_id' => $class->id,
        ]);

        $subject->teachers()->attach($teacher->id);
        $subject->students()->attach($student->id);

        QuestionBankItem::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'question' => '2 + 2 = ?',
            'option_a' => '3',
            'option_b' => '4',
            'option_c' => '5',
            'option_d' => '6',
            'correct_option' => 'B',
        ]);

        Quiz::create([
            'subject_id' => $subject->id,
            'teacher_id' => $teacher->id,
            'title' => 'Sample Quiz',
            'starts_at' => now()->subHour(),
            'deadline' => now()->addDays(2),
            'published' => true,
        ]);
    }
}