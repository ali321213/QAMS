<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\QuestionBankItem;
use App\Models\Quiz;
use App\Models\SchoolClass;
use App\Models\StudentProfile;
use App\Models\Subject;
use App\Models\TeacherProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Demo dataset for manual QAMS testing. Intended for: php artisan migrate:fresh --seed
 */
class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'user_name' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'active' => '1',
        ]);

        $teacherMath = User::create([
            'name' => 'Sara Khan',
            'user_name' => 'teacher_math',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'active' => '1',
        ]);

        $teacherEng = User::create([
            'name' => 'Ali Raza',
            'user_name' => 'teacher_eng',
            'password' => Hash::make('password'),
            'role' => 'teacher',
            'active' => '1',
        ]);

        TeacherProfile::create([
            'user_id' => $teacherMath->id,
            'job_history' => '2018–2024: Senior subject teacher, City High School.',
            'education' => 'MSc Mathematics, BS Education',
        ]);

        TeacherProfile::create([
            'user_id' => $teacherEng->id,
            'job_history' => '2016–present: English faculty, Springfield Academy.',
            'education' => 'MA English Literature',
        ]);

        $class9 = SchoolClass::create(['name' => 'Class 9']);
        $class10 = SchoolClass::create(['name' => 'Class 10']);

        $math = Subject::create(['school_class_id' => $class10->id, 'name' => 'Mathematics']);
        $english = Subject::create(['school_class_id' => $class10->id, 'name' => 'English']);
        $cs = Subject::create(['school_class_id' => $class10->id, 'name' => 'Computer Science']);
        $science9 = Subject::create(['school_class_id' => $class9->id, 'name' => 'General Science']);

        $math->teachers()->attach($teacherMath->id);
        $cs->teachers()->attach($teacherMath->id);
        $english->teachers()->attach($teacherEng->id);
        $science9->teachers()->attach($teacherEng->id);

        $studentA = User::create([
            'name' => 'Ayesha Malik',
            'user_name' => 'student_ayesha',
            'password' => Hash::make('password'),
            'role' => 'student',
            'active' => '1',
        ]);
        $studentB = User::create([
            'name' => 'Bilal Ahmed',
            'user_name' => 'student_bilal',
            'password' => Hash::make('password'),
            'role' => 'student',
            'active' => '1',
        ]);
        $studentC = User::create([
            'name' => 'Hina Noor',
            'user_name' => 'student_hina',
            'password' => Hash::make('password'),
            'role' => 'student',
            'active' => '1',
        ]);

        StudentProfile::create([
            'user_id' => $studentA->id,
            'admission_number' => 'ADM-2026-001',
            'father_name' => 'Malik Tariq',
            'picture_path' => null,
            'school_class_id' => $class10->id,
        ]);
        StudentProfile::create([
            'user_id' => $studentB->id,
            'admission_number' => 'ADM-2026-002',
            'father_name' => 'Ahmed Farooq',
            'picture_path' => null,
            'school_class_id' => $class10->id,
        ]);
        StudentProfile::create([
            'user_id' => $studentC->id,
            'admission_number' => 'ADM-2026-003',
            'father_name' => 'Noor Hussain',
            'picture_path' => null,
            'school_class_id' => $class9->id,
        ]);

        foreach ([$math, $english, $cs] as $sub) {
            $sub->students()->attach([$studentA->id, $studentB->id]);
        }
        $science9->students()->attach($studentC->id);

        $bank = [
            ['What is 7 + 8?', '14', '15', '16', '17', 'B'],
            ['What is 12 × 3?', '33', '34', '35', '36', 'D'],
            ['What is 100 ÷ 4?', '20', '25', '30', '40', 'B'],
            ['What is 9 − 4?', '4', '5', '6', '7', 'B'],
            ['What is 2³ (2 cubed)?', '6', '7', '8', '9', 'C'],
            ['What is the square root of 81?', '7', '8', '9', '10', 'C'],
            ['Convert 0.5 to a fraction.', '1/3', '1/4', '1/2', '2/3', 'C'],
            ['What is 45% of 200?', '80', '85', '90', '95', 'C'],
            ['Solve: x + 12 = 20. What is x?', '6', '7', '8', '9', 'C'],
            ['What is the perimeter of a square with side 5?', '15', '18', '20', '25', 'C'],
            ['What is 3/4 + 1/4?', '1/2', '3/4', '1', '5/4', 'C'],
            ['Which is prime: 9, 11, 15, 21?', '9', '11', '15', '21', 'B'],
            ['Round 3.78 to one decimal place.', '3.7', '3.8', '3.9', '4.0', 'B'],
            ['What is −5 + 12?', '5', '6', '7', '17', 'C'],
            ['Area of rectangle length 6, width 4?', '18', '20', '24', '28', 'C'],
            ['What is 2 + 2 × 3? (order of ops)', '8', '10', '12', '14', 'A'],
            ['What is 1/2 of 48?', '12', '20', '24', '32', 'C'],
            ['Decimal for 25%', '0.15', '0.2', '0.25', '0.3', 'C'],
            ['Hypotenuse if legs 3 and 4 (Pythagoras)?', '5', '6', '7', '12', 'A'],
            ['Next number: 2, 4, 8, 16, ?', '20', '24', '28', '32', 'D'],
            ['What is 144 ÷ 12?', '10', '11', '12', '13', 'C'],
            ['Simplify: 15/25', '1/5', '2/5', '3/5', '4/5', 'C'],
            ['What is 10²?', '20', '100', '1000', '12', 'B'],
            ['Median of 3, 7, 9?', '5', '6', '7', '9', 'C'],
            ['Mean of 4, 6, 8?', '5', '6', '7', '18', 'B'],
            ['If y = 2x and x = 5, what is y?', '7', '8', '10', '12', 'C'],
            ['Circumference formula C = 2πr; if r=1, C≈?', '3.14', '6.28', '9.42', '12.56', 'B'],
            ['What is 5!', '60', '100', '120', '720', 'C'],
            ['Binary 1010 in decimal?', '8', '9', '10', '12', 'C'],
            ['Slope of line through (0,0) and (2,4)?', '1', '2', '3', '4', 'B'],
        ];

        foreach ($bank as $row) {
            QuestionBankItem::create([
                'subject_id' => $math->id,
                'teacher_id' => $teacherMath->id,
                'question' => $row[0],
                'option_a' => $row[1],
                'option_b' => $row[2],
                'option_c' => $row[3],
                'option_d' => $row[4],
                'correct_option' => $row[5],
            ]);
        }

        $starts = Carbon::now()->subDay();
        $deadline = Carbon::now()->addDays(7);

        $quiz = Quiz::create([
            'subject_id' => $math->id,
            'teacher_id' => $teacherMath->id,
            'title' => 'Demo: Mathematics Quiz',
            'starts_at' => $starts,
            'deadline' => $deadline,
            'published' => true,
        ]);

        $assignment = Assignment::create([
            'subject_id' => $math->id,
            'teacher_id' => $teacherMath->id,
            'title' => 'Demo: Algebra worksheet',
            'description' => 'Upload a PDF or image of your solutions.',
            'deadline' => $deadline,
            'published' => true,
        ]);

        foreach ($math->students()->pluck('users.id') as $studentId) {
            AssignmentSubmission::firstOrCreate(
                ['assignment_id' => $assignment->id, 'student_id' => $studentId],
                ['status' => 'pending', 'marks' => 0]
            );
        }
    }
}
