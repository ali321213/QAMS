# QAMS – Testing Guide

This document explains every test in the suite, how to run them, and which functional requirement each test covers.

---

## Quick start

> No database server needed. Tests run on an in-memory SQLite database — XAMPP does **not** need to be running.

```bash
# Run the full suite (from the project root)
php artisan test

# Run one file at a time
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Feature/AdminTest.php
php artisan test tests/Feature/TeacherTest.php
php artisan test tests/Feature/StudentTest.php
php artisan test tests/Feature/SystemTest.php

# Run a single test by name
php artisan test --filter "admin can block a user"

# Run with verbose output (show each test name)
php artisan test --verbose
```

Expected result: **89 tests, 183 assertions, 0 failures**

---

## Test file overview

| File | Tests | Covers |
|---|---|---|
| `AuthTest.php` | 17 | Login, logout, blocked accounts, role redirects |
| `AdminTest.php` | 21 | All 8 Admin requirements |
| `TeacherTest.php` | 19 | All 8 Teacher requirements |
| `StudentTest.php` | 17 | All 3 Student requirements |
| `SystemTest.php` | 11 | Both System requirements (auto-zero, auto-mark) |
| **Total** | **89** | Every functional requirement |

---

## `AuthTest.php` — Authentication (17 tests)

| Test name | What it checks |
|---|---|
| `root url redirects to login` | Visiting `/` redirects unauthenticated user to the login page |
| `guest is redirected to login for protected routes` (5 data sets) | Visiting dashboard / admin / teacher / student pages without login redirects to login |
| `user can register via form` | Filling the `/register` form creates a new user account |
| `registration fails with duplicate username` | Trying to register with an existing username shows a validation error |
| `user can login with correct credentials` | Correct username + password logs the user in |
| `login fails with wrong password` | Wrong password returns a validation error, user stays logged out |
| `login fails for non existent username` | Unknown username returns a validation error |
| `blocked account cannot login` | A blocked user (`active=0`) cannot log in |
| `admin is redirected to admin dashboard` | After login, admin lands on `/admin/dashboard` |
| `teacher is redirected to teacher dashboard` | After login, teacher lands on `/teacher/dashboard` |
| `student is redirected to student dashboard` | After login, student lands on `/student/dashboard` |
| `authenticated user can logout` | `POST /logout` invalidates the session |
| `blocked user is logged out on next request` | If an account is blocked mid-session, the next page visit forces logout |
| `student cannot access teacher routes` | Student gets 403 on `/teacher/dashboard` |
| `teacher cannot access admin dashboard` | Teacher is redirected away from `/admin/dashboard` (not 403 — this is by design in `EnsureUserIsAdmin`) |
| `student cannot access admin dashboard` | Student is redirected away from `/admin/dashboard` |

---

## `AdminTest.php` — Admin requirements (21 tests)

### Requirement #2 — Classes & Subjects

| Test name | What it checks |
|---|---|
| `admin can create a class` | POST `/admin/qams/classes` creates a new class, returns 201 |
| `admin cannot create duplicate class` | Duplicate class name returns 422 Unprocessable |
| `admin can update a class` | PUT `/admin/qams/classes/{id}` updates the class name |
| `admin can create a subject` | POST `/admin/qams/subjects` creates a subject linked to a class |
| `admin can update a subject` | PUT `/admin/qams/subjects/{id}` updates subject details |

### Requirement #3 — Register student

| Test name | What it checks |
|---|---|
| `admin can register student with full details` | Creates user + `student_profiles` row + subject enrollment |
| `admission number must be unique` | Duplicate admission number returns 422 |

### Requirement #4 — Register teacher

| Test name | What it checks |
|---|---|
| `admin can register teacher with profile` | Creates user + `teacher_profiles` row with job history & education |
| `admin cannot register teacher with duplicate username` | Duplicate username returns 422 |

### Requirement #5 — Update info

| Test name | What it checks |
|---|---|
| `admin can update student info` | PUT updates user name and `student_profiles.father_name` |
| `admin can update teacher info` | PUT updates user name and `teacher_profiles.education` |

### Requirement #6 — Assign subjects to teachers

| Test name | What it checks |
|---|---|
| `admin can assign subject to teacher` | POST creates entry in `subject_teacher` pivot |
| `admin cannot assign a student as teacher to subject` | Returns 422 if the selected user is not a teacher |

### Requirement #7 — Block / Unblock

| Test name | What it checks |
|---|---|
| `admin can block a user` | `active` flips from `1` → `0` |
| `admin can unblock a blocked user` | `active` flips from `0` → `1` |
| `admin cannot block their own account` | Returns a validation error, own account stays active |

### Requirement #8 — Reports & Search

| Test name | What it checks |
|---|---|
| `admin reports return correct counts` | `GET /admin/qams/reports` returns correct student/teacher/subject/class counts |
| `admin search finds teachers and admins not only students` | **Bug 2 regression** — search works across all roles, not students only |
| `admin search without term returns all users` | Without a search term, all users of all roles are listed |

### Access control

| Test name | What it checks |
|---|---|
| `teacher cannot access admin qams routes` | Teacher gets 403 on admin management routes |
| `student cannot access admin qams routes` | Student gets 403 on admin management routes |

---

## `TeacherTest.php` — Teacher requirements (19 tests)

### Requirement #2 — Question bank

| Test name | What it checks |
|---|---|
| `teacher can add question to assigned subject` | Question saved with correct subject and teacher; marked with `correct_option` |
| `teacher cannot add question to unassigned subject` | Returns 403 when subject is not assigned to this teacher |
| `question bank index only shows assigned subjects` | Page does not show questions from other teachers' subjects |

### Requirement #3 — Quizzes

| Test name | What it checks |
|---|---|
| `teacher can create a quiz` | Quiz created as draft (`published=0`) with start time and deadline |
| `teacher can publish a quiz` | `published` flag changes to `1` |
| `teacher cannot publish another teachers quiz` | Returns 403 — ownership enforced |

### Requirement #4 — Assignments

| Test name | What it checks |
|---|---|
| `teacher can create assignment` | Assignment created as draft with deadline and description |
| `creating assignment auto creates student submission records` | `assignment_submissions` row created for every enrolled student immediately |
| `teacher can publish assignment` | `published` flag changes to `1` |

### Requirement #5 — Extend deadlines

| Test name | What it checks |
|---|---|
| `teacher can extend quiz deadline` | Deadline column updated in database |
| `teacher cannot extend another teachers assignment` | Returns 403 |
| `teacher can extend assignment deadline` | Deadline column updated in database |

### Requirement #6 — View only own data (submissions)

| Test name | What it checks |
|---|---|
| `teacher can view assignment submissions` | Submissions page shows enrolled students |
| `teacher cannot view another teachers submissions` | Returns 403 |

### Requirement #8 — Grade assignments

| Test name | What it checks |
|---|---|
| `teacher can grade assignment submission` | `marks`, `status=graded`, `feedback` saved; `graded_by` set |
| `grade marks must be between 0 and 100` | Marks of 150 returns 422 |

### Requirement #9 — Performance reports

| Test name | What it checks |
|---|---|
| `teacher can view performance report for assigned subject` | Returns JSON with `quiz_average`, `assignment_average`, `generated_at` |
| `teacher cannot view performance report for unassigned subject` | Returns 403 |
| `performance report page does not crash when teacher has no subjects` | **Bug 3 regression** — page returns 200 instead of crashing with TypeError |

---

## `StudentTest.php` — Student requirements (17 tests)

### Requirement #2 — Quizzes

| Test name | What it checks |
|---|---|
| `student can see published quizzes for enrolled subjects` | Quiz list page shows the quiz title |
| `student cannot see unpublished quiz` | Draft quiz is hidden from the list |
| `student cannot see quizzes for unenrolled subjects` | Quiz from a different subject does not appear |
| `quiz attempt page loads without error` | **Bug 1 regression** — attempt page renders without PHP parse error |
| `student can attempt quiz and score is calculated` | Submitting correct answer returns `score: 1` |
| `wrong answer scores zero` | Submitting wrong answer returns `score: 0` |
| `student cannot attempt same quiz twice` | Returns 422 on a second attempt |
| `student cannot attempt quiz after deadline` | Returns 403 when deadline has passed |
| `student cannot attempt quiz for unenrolled subject` | Returns 403 for a student not enrolled in that subject |
| `student cannot attempt unpublished quiz` | Returns 403 for a draft quiz |

### Requirement #3 — Assignment submission

| Test name | What it checks |
|---|---|
| `student can submit assignment before deadline` | File stored; submission status is `pending` |
| `student submitting after deadline gets auto zero status` | Status saved as `auto_zero`, marks = 0 |
| `student cannot submit for unenrolled subject` | Returns 403 |
| `student cannot submit to unpublished assignment` | Returns 403 |
| `student can view assignment details` | Assignment show page renders correctly |

### Requirement #4 — Results

| Test name | What it checks |
|---|---|
| `student can view quiz and assignment results` | JSON endpoint returns own quiz scores and assignment marks |
| `student can view results page` | HTML page loads and shows "Quiz results" and "Assignments" headings |
| `results only show own records` | Another student's attempt does not appear in results |

---

## `SystemTest.php` — System requirements (11 tests)

### System requirement #1 — Auto-zero marks

| Test name | What it checks |
|---|---|
| `pending submission with no file after deadline gets auto zero` | `applyAutoZeroMarks()` sets `status=auto_zero`, `marks=0` |
| `pending submission before deadline is not touched` | Active deadline: status stays `pending` |
| `already graded submission is not overwritten by auto zero` | Graded submissions are never overwritten |
| `multiple overdue submissions are all zeroed` | Batch of 4 overdue submissions all updated in one call |
| `auto zero is triggered via middleware on each request` | Visiting any page while logged in automatically triggers the zero-mark process |

### System requirement #2 — Auto-mark quizzes

| Test name | What it checks |
|---|---|
| `all correct answers give full score` | 2 correct answers → score 2 |
| `all wrong answers give zero score` | 2 wrong answers → score 0 |
| `mixed answers give partial score` | 2 correct + 1 wrong out of 3 → score 2 |
| `each answer is stored with correct is correct flag` | `quiz_answers` table stores `is_correct=1` and `is_correct=0` correctly |
| `score is stored in quiz attempt record` | `quiz_attempts.score` matches the calculated score |
| `questions from other subjects are rejected` | Submitting a question from a different subject returns 422 |

---

## How the tests are isolated

- Every test class uses `RefreshDatabase` — the in-memory SQLite database is completely wiped and re-migrated before each test.
- `Carbon::setTestNow(...)` is used in time-sensitive tests so deadlines and auto-zero logic behave predictably.
- `Storage::fake('public')` is used for file upload tests — no real files are written to disk.
- No XAMPP, no MySQL, no running server is required.

---

## Bugs covered by regression tests

| Bug | Regression test |
|---|---|
| Bug 1 — PHP parse error in quiz attempt blade | `quiz attempt page loads without error` (StudentTest) |
| Bug 2 — Admin search only returned students | `admin search finds teachers and admins not only students` (AdminTest) |
| Bug 3 — TypeError when teacher has no subjects | `performance report page does not crash when teacher has no subjects` (TeacherTest) |
