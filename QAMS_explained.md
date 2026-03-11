## QAMS - Quiz and Assignment Management System

This document explains the main parts of the QAMS application: data model, roles, backend logic, and UI.

---

### 1. Core Concepts and Roles

- **Admin**
  - Manages users (admins, teachers, students).
  - Can block/unblock accounts.
  - Can be extended to manage classes/subjects and reports.

- **Teacher**
  - Has a `User` record with role `teacher` and a linked `Teacher` profile.
  - Can create quizzes with questions (question bank per quiz).
  - Can publish quizzes and view automatic results.
  - Can create assignments, extend deadlines, grade submissions, and auto-assign zero marks to missing submissions.

- **Student**
  - Has a `User` record with role `student` and a linked `Student` profile.
  - Belongs to a class and indirectly to subjects given to that class.
  - Can attempt quizzes within the allowed time.
  - Can upload assignment solutions before deadlines and see their own results.

---

### 2. Database Design (Migrations & Models)

#### 2.1 Users and Profiles
- `users` (existing)
  - Fields: `id`, `name`, `user_name`, `password`, `role` (`admin|teacher|student`), `active`.
  - Model: `User` (`app/Models/User.php`)
    - Helper methods: `isActive()`, `isAdmin()`.
    - Relations:
      - `teacher()` → one `Teacher` profile.
      - `student()` → one `Student` profile.

- `teachers` (`2026_03_11_100000_create_academic_structure_tables.php`)
  - Fields: `user_id`, `job_history`, `education`.
  - Model: `Teacher`
    - `user()` → owning `User`.
    - `subjects()` → taught subjects (many-to-many).
    - `quizzes()`, `assignments()` → created items.

- `students`
  - Fields: `user_id`, `admission_number`, `father_name`, `photo_path`, `class_id`.
  - Model: `Student`
    - `user()` → owning `User`.
    - `class()` → `SchoolClass`.
    - `quizAttempts()`, `assignmentSubmissions()` → activity and results.

#### 2.2 Academic Structure
- `classes`
  - Minimal class entity with `name` and optional `section`.
  - Model: `SchoolClass`
    - `subjects()`, `students()`.

- `subjects`
  - Each subject is attached to a specific class.
  - Model: `Subject`
    - `class()` → `SchoolClass`.
    - `teachers()` → many teachers may handle the subject.
    - `quizzes()`, `assignments()`.

- `subject_teacher`
  - Pivot linking subjects and teachers.
  - Ensures which teacher can act on which subject.

#### 2.3 Quizzes and Auto-Marking
- `quizzes`
  - Fields: `title`, `description`, `subject_id`, `teacher_id`,
    `starts_at`, `ends_at`, `is_published`.
  - Model: `Quiz`
    - `subject()`, `teacher()`, `questions()`, `attempts()`.

- `quiz_questions`
  - Question bank per quiz.
  - Fields: `question_text`, 4 options (`option_a`–`option_d`),
    `correct_option`, `marks`.
  - Model: `QuizQuestion`
    - `quiz()`, `answers()`.

- `quiz_attempts`
  - Per-student record of attempting a quiz.
  - Fields: `quiz_id`, `student_id`, timestamps `started_at`, `submitted_at`,
    `total_score`, `status` (`in_progress|submitted`).
  - Model: `QuizAttempt`
    - `quiz()`, `student()`, `answers()`.

- `quiz_answers`
  - Per-question answer within an attempt.
  - Fields: `quiz_attempt_id`, `question_id`, `selected_option`,
    `is_correct`, `earned_marks`.
  - Model: `QuizAnswer`
    - `attempt()`, `question()`.

**Auto-marking logic** (in `StudentController::submitQuiz`):
- Student submits answers via `answers[question_id] = selected_option`.
- For each question in the quiz:
  - Compare `selected_option` with `question.correct_option`.
  - If correct: set `is_correct = true` and `earned_marks = question.marks`.
  - Accumulate total score across all questions.
- Save result to `quiz_attempts.total_score` and mark status `submitted`.
- This fully automates quiz marking.

#### 2.4 Assignments and Auto-Zero
- `assignments`
  - Fields: `title`, `description`, `subject_id`, `teacher_id`,
    `assigned_at`, `deadline_at`, `extended_deadline_at`, `is_closed`.
  - Model: `Assignment`
    - `subject()`, `teacher()`, `submissions()`.
    - Helper: `effectiveDeadline()` → uses extended deadline if present.

- `assignment_submissions`
  - Each student’s upload and grade for an assignment.
  - Fields: `assignment_id`, `student_id`, `file_path`, `submitted_at`,
    `marks`, `feedback`, `status`
    (`pending|submitted|graded|auto_zero`).
  - Model: `AssignmentSubmission`
    - `assignment()`, `student()`.

**Zero marks for late / missing submissions**:
- Implemented in `TeacherController::autoZeroMissingSubmissions`.
- Steps when teacher triggers the action:
  1. Check `now()` is after `assignment.effectiveDeadline()`.
  2. Get list of students in the assignment’s class.
  3. For each student without a submission:
     - Create `assignment_submissions` row with `marks = 0`,
       `status = 'auto_zero'`, and feedback
       “Not submitted on time. Auto-assigned zero.”
- This fulfills the requirement that the system automatically assigns zero if a student does not submit on time.

---

### 3. Role-Based Access and Middleware

- Guest routes:
  - Registration and login handled by `AuthController`.
- Authenticated routes:
  - Logout and generic `/dashboard` handled by `DashboardController`.
  - If user is admin → redirected to admin dashboard.
  - If teacher or student → they can go to their role dashboards (`/teacher/dashboard`, `/student/dashboard`).

**Admin routes** (`routes/web.php`):
- Prefix: `/admin`, name: `admin.*`, middleware: `admin`.
- Uses existing `AdminController` for user management:
  - `admin.dashboard` – list/search users.
  - `admin.users.edit`, `admin.users.update` – update profile and role.
  - `admin.users.toggle-block` – block/unblock users.

**Teacher routes**:
- Prefix: `/teacher`, name: `teacher.*`.
- Middleware: `EnsureUserHasRole::class . ':teacher'` – only teachers allowed.
- Controller: `TeacherController`.
  - Quiz management routes:
    - `teacher.dashboard` – basic teacher overview.
    - `teacher.quizzes.index` / `.create` / `.store` / `.edit` / `.update` / `.publish` / `.results`.
  - Assignment management routes:
    - `teacher.assignments.index` / `.create` / `.store` / `.edit` / `.update`.
    - `teacher.assignments.extend-deadline`.
    - `teacher.assignments.submissions` – view all submissions for a given assignment.
    - `teacher.assignments.grade` – grade and give feedback.
    - `teacher.assignments.auto-zero` – automatically create zero-mark submissions for missing students.

**Student routes**:
- Prefix: `/student`, name: `student.*`.
- Middleware: `EnsureUserHasRole::class . ':student'`.
- Controller: `StudentController`.
  - `student.dashboard` – summary of upcoming quizzes and pending assignments.
  - Quiz routes:
    - `student.quizzes.index` – list quizzes for the student’s class.
    - `student.quizzes.show` – show quiz and options.
    - `student.quizzes.start` – (idempotent) ensure an attempt exists.
    - `student.quizzes.submit` – submit answers and trigger auto-marking.
  - Assignment routes:
    - `student.assignments.index` – list assignments for the student’s class.
    - `student.assignments.show` – view details, status, and submission.
    - `student.assignments.submit` – upload assignment file (stored on `public` disk under `assignments/`).
  - Reports:
    - `student.reports.performance` – combined quiz and assignment performance view.

**Custom middleware**:
- `EnsureUserHasRole`:
  - Verifies `Auth::user()->role === $role` (`teacher` or `student`).
  - Redirects to generic `dashboard` if the role does not match.

Laravel’s class-based middleware reference is used directly in routes,
so no change to `Kernel.php` is required.

---

### 4. Controllers and Logic Flow

#### 4.1 `TeacherController`
- **Dashboard**
  - Shows counts of quizzes and assignments created by the logged-in teacher.

- **Quiz CRUD**
  - Uses `Teacher::subjects()` so teachers can only create quizzes for their assigned subjects.
  - `storeQuiz`:
    - Validates quiz metadata plus a dynamic array of questions.
    - Creates `Quiz` then loops to create `QuizQuestion` records.
  - `publishQuiz`:
    - Sets `is_published = true` to make it visible to students.
  - `quizResults`:
    - Loads `QuizAttempt` records with associated students for result display.

- **Assignments**
  - `storeAssignment` / `updateAssignment`:
    - Create or update assignment metadata and deadlines.
  - `extendAssignmentDeadline`:
    - Sets `extended_deadline_at`, which is always used via `effectiveDeadline()`.
  - `viewAssignmentSubmissions`:
    - Loads all `AssignmentSubmission` records with student info.
  - `gradeAssignmentSubmission`:
    - Teacher sets `marks` and optional `feedback` and marks status `graded`.
  - `autoZeroMissingSubmissions`:
    - Implements the automatic zero-marks rule after the effective deadline.

#### 4.2 `StudentController`
- **Dashboard**
  - Loads:
    - Upcoming quizzes for the student’s class that are published and not ended.
    - Pending assignments for the student’s class whose deadlines have not passed.

- **Quiz flow**
  - `listQuizzes`:
    - Shows all quizzes for the student’s class with current attempt status.
  - `showQuiz`:
    - Ensures quiz is published, for the correct class, and within its time window.
    - Renders all questions and options.
  - `submitQuiz`:
    - For each question, computes if the selected option is correct and awards marks.
    - Saves total score and marks attempt as submitted (auto-marking).

- **Assignment flow**
  - `listAssignments`:
    - Shows assignments for the class with submission statuses.
  - `showAssignment`:
    - Displays metadata, description, and the student’s submission (if any).
    - If before deadline, shows file upload form.
  - `submitAssignment`:
    - Validates file and stores it via the `public` disk.
    - Creates/updates `AssignmentSubmission` with status `submitted`.

- **Performance report**
  - Combines quiz attempts and assignment submissions into one page so students can track their performance.

---

### 5. Blade Views and UI

All views extend `layouts.app`, which:
- Sets global HTML structure and typography.
- Pulls in Vite assets `resources/css/app.css` and `resources/js/app.js`.
- Gives a modern, clean UI with cards, tables, and buttons.

Key view groups:
- Admin:
  - `admin/dashboard.blade.php` – user management.
  - `admin/edit.blade.php` – edit user details and role.

- Teacher:
  - `teacher/dashboard.blade.php` – overview cards and navigation.
  - `teacher/quizzes/*.blade.php` – list, create, edit, and view results of quizzes.
  - `teacher/assignments/*.blade.php` – list, create, edit, view/grade submissions, and trigger auto-zero.

- Student:
  - `student/dashboard.blade.php` – quick access and upcoming items.
  - `student/quizzes/*.blade.php` – list and attempt quizzes.
  - `student/assignments/*.blade.php` – list assignments, see status, and submit files.
  - `student/reports/performance.blade.php` – view performance across quizzes and assignments.

Auth views:
- `auth/login.blade.php` and `auth/register.blade.php` provide the entry point for all roles.

---

### 6. How It All Fits Together

1. **Admin** creates users and (optionally) links them to `Teacher`/`Student` profiles and classes/subjects.
2. **Teacher**:
   - Defines quizzes with questions and publishes them.
   - Uploads assignments with clear deadlines and can extend them.
   - Grades assignments and can automatically assign zero marks for missing submissions after deadline.
3. **Student**:
   - Logs in and sees upcoming quizzes and assignments.
   - Attempts quizzes; the system automatically calculates marks.
   - Uploads assignment files before deadlines.
   - Views all results and performance reports.

This matches the functional requirements:
- Reduced manual marking through automatic quiz evaluation.
- Automatic zero marks for late/missing assignments.
- Role-based dashboards and clear separation of admin/teacher/student responsibilities.

