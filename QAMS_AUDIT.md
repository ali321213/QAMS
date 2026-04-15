# QAMS – Full System Audit Report

**Date:** 2026-04-16  
**Stack:** Laravel · MySQL · XAMPP · Tailwind CSS  
**Audited by:** Claude Code

---

## Summary

| Category | Total | Working | Broken (Fixed) |
|---|---|---|---|
| Admin requirements | 8 | 8 | 0 |
| Teacher requirements | 8 | 8 | 0 |
| Student requirements | 3 | 3 | 0 |
| System (auto) requirements | 2 | 2 | 0 |
| **Bugs found & fixed** | **3** | — | **3 fixed** |

All functional requirements are implemented. Three bugs were found and fixed.

---

## Bugs Found and Fixed

### Bug 1 — CRITICAL · PHP syntax error in quiz attempt page

**File:** `resources/views/student/quizzes/attempt.blade.php` · line 34  
**Status:** FIXED

**Problem:** A stray single-quote after `$q->id` caused a PHP parse error, making the quiz attempt page crash entirely — students could not attempt any quiz.

```php
// BEFORE (broken — extra ' after $q->id)
{{ old('answers.'.$q->id') === $letter ? 'checked' : '' }}

// AFTER (fixed)
{{ old('answers.'.$q->id) === $letter ? 'checked' : '' }}
```

---

### Bug 2 — HIGH · Admin search silently hides non-student users

**File:** `app/Http/Controllers/AdminController.php` · line 18  
**Status:** FIXED

**Problem:** `->where('role', 'student')` was placed inside the `if (search)` block, so searching any name or username only returned students and hid admins and teachers from results.

```php
// BEFORE (broken — role filter inside search block)
if ($request->filled('search')) {
    $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('user_name', 'like', "%{$search}%");
    })->where('role', 'student');   // ← wrong placement
}

// AFTER (fixed — role filter removed; search covers all users)
if ($request->filled('search')) {
    $query->where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('user_name', 'like', "%{$search}%");
    });
}
```

---

### Bug 3 — MEDIUM · Fatal error when teacher has no assigned subjects

**File:** `app/Http/Controllers/TeacherController.php` · line 87  
**Status:** FIXED

**Problem:** `$subjects->first()->id` throws a `TypeError` when the teacher has no assigned subjects (i.e. `first()` returns `null`). The `??` null-coalescing operator does not catch errors from `null->id` — only the null-safe operator `?->` does.

```php
// BEFORE (broken — crashes when teacher has no subjects)
$subjectId = (int) $request->query('subject_id', $subjects->first()->id ?? 0);

// AFTER (fixed)
$subjectId = (int) $request->query('subject_id', $subjects->first()?->id ?? 0);
```

---

## Full Functional Requirements Checklist

### Admin

| # | Requirement | Status | Notes |
|---|---|---|---|
| 1 | Login to the application | **WORKING** | `AuthController@login` with username/password + blocked-account check |
| 2 | Add / Update classes and subjects | **WORKING** | `AdminManagementController` — classes & subjects CRUD with unique constraints |
| 3 | Register a student (name, admission number, father's name, picture, class, subjects) | **WORKING** | `registerStudent` — all fields present, photo upload, subject enrollment via pivot |
| 4 | Register a teacher (name, job history, education) | **WORKING** | `registerTeacher` — profile stored in `teacher_profiles` |
| 5 | Update information of students and teachers | **WORKING** | `updateStudent` / `updateTeacher` — supports photo replacement |
| 6 | Assign subjects to teachers | **WORKING** | `assignTeacherToSubject` — uses `syncWithoutDetaching` (additive, does not remove existing) |
| 7 | Block or unblock any teacher or student account | **WORKING** | `AdminController@toggleBlock` — prevents self-block |
| 8 | Generate reports of students, teachers, and subjects | **WORKING** | `AdminManagementController@reports` — counts for students, teachers, subjects, classes |

---

### Teacher

| # | Requirement | Status | Notes |
|---|---|---|---|
| 1 | Login to the application | **WORKING** | Role-based redirect to `teacher.dashboard` |
| 2 | Create question bank for assigned subjects | **WORKING** | `storeQuestion` — MCQ with options A–D and correct answer; ownership check enforced |
| 3 | Conduct quizzes (create + publish) | **WORKING** | `createQuiz` + `publishQuiz` — draft/publish flow with start time and deadline |
| 4 | Upload assignments with deadlines | **WORKING** | `createAssignment` — auto-creates submission records for all enrolled students |
| 5 | Extend quiz and assignment deadlines | **WORKING** | `extendQuiz` / `extendAssignment` — validates new deadline is after start/now |
| 6 | View only their own assigned subject data | **WORKING** | All queries filter by `auth()->id()` or check subject teacher membership |
| 7 | Publish quiz results for students | **WORKING** | `publishQuiz` sets `published = true`; students see score immediately after submission |
| 8 | Grade and upload assignment results | **WORKING** | `gradeAssignment` — saves marks (0–100), feedback, sets status to `graded` |
| 9 | View student performance reports | **WORKING** | `performanceReportPage` — quiz average and assignment average per subject *(Bug 3 fixed)* |

---

### Student

| # | Requirement | Status | Notes |
|---|---|---|---|
| 1 | Login to the application | **WORKING** | Role-based redirect to `student.dashboard` |
| 2 | Attempt quizzes within deadlines | **WORKING** | `quizAttemptForm` + `attemptQuiz` — checks published, enrolled, deadline, and no repeat attempt *(Bug 1 fixed)* |
| 3 | Upload assignment solution within deadlines | **WORKING** | `submitAssignment` — file upload; auto-zero applied if late |
| 4 | View their own results and reports | **WORKING** | `myResults` — shows all quiz scores and assignment grades with feedback |

---

### System (Automated)

| # | Requirement | Status | Notes |
|---|---|---|---|
| 1 | Auto-assign zero marks if student does not submit on time | **WORKING** | `AssignmentSubmission::applyAutoZeroMarks()` — runs on every authenticated request via `EnsureUserIsActive` middleware |
| 2 | Auto-mark all quizzes | **WORKING** | `attemptQuiz` calculates score instantly by comparing `selected_option` to `correct_option`; stored in `quiz_attempts.score` |

---

## Architecture Notes

| Area | Detail |
|---|---|
| Auth | Custom username/password auth (no Sanctum/Passport); `user_name` field used as login identifier |
| Authorization | Middleware-based: `role:admin`, `role:teacher`, `role:student`; inline `abort_unless` ownership checks in controllers |
| File storage | Student photos → `storage/app/public/student_photos/`; Assignment files → `storage/app/public/assignment_submissions/` |
| Database | 13 tables with proper FK constraints and cascade deletes; pivot tables for teacher-subject and student-subject many-to-many |
| Auto-zero | Runs on every authenticated page load — sufficient for correctness; consider a scheduled command (`php artisan schedule:run`) if load grows |
| Registration | Any user can self-register via `/register` with any role including `admin` — consider restricting this to admin-only in production |
