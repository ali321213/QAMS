<?php

namespace Database\Seeders;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::updateOrCreate(
            ['user_name' => 'admin'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'active' => '1',
            ]
        );

        // Classes
        $class10A = SchoolClass::firstOrCreate(['name' => 'Class 10', 'section' => 'A']);
        $class10B = SchoolClass::firstOrCreate(['name' => 'Class 10', 'section' => 'B']);

        // Subjects
        $math10A = Subject::firstOrCreate([
            'name' => 'Mathematics',
            'class_id' => $class10A->id,
        ]);
        $science10A = Subject::firstOrCreate([
            'name' => 'Science',
            'class_id' => $class10A->id,
        ]);

        // Teachers and profiles
        $teacher1User = User::updateOrCreate(
            ['user_name' => 'teacher1'],
            [
                'name' => 'Teacher One',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'active' => '1',
            ]
        );
        $teacher1 = Teacher::firstOrCreate(
            ['user_id' => $teacher1User->id],
            ['job_history' => '5 years teaching experience', 'education' => 'MSc Mathematics']
        );

        $teacher2User = User::updateOrCreate(
            ['user_name' => 'teacher2'],
            [
                'name' => 'Teacher Two',
                'password' => Hash::make('password'),
                'role' => 'teacher',
                'active' => '1',
            ]
        );
        $teacher2 = Teacher::firstOrCreate(
            ['user_id' => $teacher2User->id],
            ['job_history' => '3 years teaching experience', 'education' => 'MSc Physics']
        );

        // Attach teachers to subjects
        $teacher1->subjects()->syncWithoutDetaching([$math10A->id]);
        $teacher2->subjects()->syncWithoutDetaching([$science10A->id]);

        // Students and profiles
        $students = [];
        for ($i = 1; $i <= 5; $i++) {
            $user = User::updateOrCreate(
                ['user_name' => 'student'.$i],
                [
                    'name' => 'Student '.$i,
                    'password' => Hash::make('password'),
                    'role' => 'student',
                    'active' => '1',
                ]
            );

            $students[] = Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'admission_number' => 'ADM10A'.$i,
                    'father_name' => 'Father '.$i,
                    'photo_path' => null,
                    'class_id' => $class10A->id,
                ]
            );
        }

        // Sample quizzes for Mathematics and Science (3–4 quizzes, 10 questions each)
        $quizDefinitions = [
            [
                'title' => 'Maths Quiz 1 - Algebra Basics',
                'subject' => $math10A,
                'teacher' => $teacher1,
                'description' => 'Linear equations and simple algebraic manipulation.',
            ],
            [
                'title' => 'Maths Quiz 2 - Arithmetic',
                'subject' => $math10A,
                'teacher' => $teacher1,
                'description' => 'Addition, subtraction, multiplication, and division.',
            ],
            [
                'title' => 'Science Quiz 1 - Physics Basics',
                'subject' => $science10A,
                'teacher' => $teacher2,
                'description' => 'Motion, force, and simple machines.',
            ],
            [
                'title' => 'Science Quiz 2 - Chemistry Basics',
                'subject' => $science10A,
                'teacher' => $teacher2,
                'description' => 'Elements, compounds, and mixtures.',
            ],
        ];

        foreach ($quizDefinitions as $index => $def) {
            $quiz = Quiz::firstOrCreate(
                [
                    'title' => $def['title'],
                    'subject_id' => $def['subject']->id,
                    'teacher_id' => $def['teacher']->id,
                ],
                [
                    'description' => $def['description'],
                    'starts_at' => now()->subMinutes(30),
                    'ends_at' => now()->addDays(2 + $index),
                    'is_published' => true,
                ]
            );

            if ($quiz->questions()->count() === 0) {
                for ($q = 1; $q <= 10; $q++) {
                    QuizQuestion::create([
                        'quiz_id' => $quiz->id,
                        'question_text' => "Q{$q}: Sample question {$q} for {$def['title']}?",
                        'option_a' => 'Option A',
                        'option_b' => 'Option B',
                        'option_c' => 'Option C',
                        'option_d' => 'Option D',
                        'correct_option' => 'b',
                        'marks' => 1,
                    ]);
                }
            }
        }

        // Sample assignments (4–5 assignments across subjects)
        $assignmentDefinitions = [
            [
                'title' => 'Algebra Assignment 1',
                'subject' => $math10A,
                'teacher' => $teacher1,
                'description' => 'Solve the given algebraic expressions and upload your solutions as a PDF.',
                'daysOffset' => 3,
            ],
            [
                'title' => 'Algebra Assignment 2',
                'subject' => $math10A,
                'teacher' => $teacher1,
                'description' => 'Word problems on linear equations in one variable.',
                'daysOffset' => 5,
            ],
            [
                'title' => 'Science Assignment 1 - Lab Report',
                'subject' => $science10A,
                'teacher' => $teacher2,
                'description' => 'Write a lab report on your recent physics experiment.',
                'daysOffset' => 4,
            ],
            [
                'title' => 'Science Assignment 2 - Research',
                'subject' => $science10A,
                'teacher' => $teacher2,
                'description' => 'Research a renewable energy source and submit a short report.',
                'daysOffset' => 6,
            ],
            [
                'title' => 'Maths Revision Assignment',
                'subject' => $math10A,
                'teacher' => $teacher1,
                'description' => 'Mixed practice of arithmetic and algebra questions.',
                'daysOffset' => 7,
            ],
        ];

        foreach ($assignmentDefinitions as $idx => $def) {
            $assignment = Assignment::firstOrCreate(
                [
                    'title' => $def['title'],
                    'subject_id' => $def['subject']->id,
                    'teacher_id' => $def['teacher']->id,
                ],
                [
                    'description' => $def['description'],
                    'assigned_at' => now()->subDay(),
                    'deadline_at' => now()->addDays($def['daysOffset']),
                    'extended_deadline_at' => null,
                    'is_closed' => false,
                ]
            );

            // For the first assignment only, add one graded sample submission
            if ($idx === 0 && $assignment->submissions()->count() === 0 && isset($students[0])) {
                AssignmentSubmission::create([
                    'assignment_id' => $assignment->id,
                    'student_id' => $students[0]->id,
                    'file_path' => null,
                    'submitted_at' => now()->subHours(2),
                    'marks' => 8,
                    'feedback' => 'Good work!',
                    'status' => 'graded',
                ]);
            }
        }
    }
}