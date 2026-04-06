# QAMS Complete App Testing Flow

This guide gives click-by-click testing for all major flows in the app.

## 1) Pre-Setup (Run Once)

1. Open terminal in project root.
2. Run:
   - `php artisan migrate:fresh --seed`
   - `php artisan storage:link`
   - `php artisan serve`
3. Open browser at:
   - `http://127.0.0.1:8000/login`

## 2) Test Credentials

- Admin: `admin` / `password`
- Teacher: `teacher1` / `password`
- Student: `student1` / `password`

---

## 3) Admin Flow (End-to-End)

### A. Login + User Management

1. Login with admin credentials.
2. You should land on **Admin dashboard** (`/admin/dashboard`).
3. In **User management** table:
   - Search a user by name.
   - Click `Edit` on teacher/student and update details.
   - Click `Block` and `Unblock` for a non-admin user.
4. Expected:
   - Success messages appear.
   - Status changes between Active/Blocked.

### B. Open QAMS Admin Hub

1. On admin dashboard, click **Open QAMS admin**.
2. You should land on `/admin/qams`.

### C. Classes

1. Click **Classes**.
2. Add a class from the form.
3. Edit an existing class name and click Save.
4. Expected:
   - Class appears in list.
   - Updates save successfully.

### D. Subjects + Teacher Assignment

1. Click **Subjects**.
2. Add a subject by selecting class + subject name.
3. In subject card:
   - Update subject/class.
   - Assign a teacher from dropdown and click **Assign**.
4. Expected:
   - Subject saved.
   - Assigned teacher shows in chips/list.

### E. Teacher Registration/Update

1. Click **Teachers**.
2. Click **Register teacher**, fill form, submit.
3. From teachers list click **Edit**, update info, submit.
4. Expected:
   - Teacher record is created and editable.

### F. Student Registration/Update

1. Click **Students**.
2. Click **Register student**.
3. Fill:
   - Name, username, password, admission number, father name
   - Class
   - Subjects (checkboxes)
   - Optional photo
4. Submit form.
5. Edit same student and update details.
6. Expected:
   - Student appears in listing.
   - Profile and subject enrollment persist.

### G. Reports

1. Click **Reports**.
2. Verify cards show counts:
   - Students, Teachers, Subjects, Classes.
3. Expected:
   - Counts match your seeded/created data.

### H. Logout

1. Click Logout.

---

## 4) Teacher Flow (End-to-End)

### A. Login + Dashboard

1. Login as teacher (`teacher1`).
2. You should land on `/teacher/dashboard`.
3. Verify cards/links:
   - Question bank
   - Quizzes
   - Assignments
   - Performance report

### B. Question Bank

1. Click **Question bank**.
2. Add a question:
   - Subject
   - Question text
   - Options A/B/C/D
   - Correct option
3. Submit.
4. Expected:
   - Success message.
   - New question appears in question list.

### C. Create Quiz + Publish + Extend

1. Click **Quizzes**.
2. Click **Create quiz**.
3. Fill subject, title, starts_at, deadline and submit.
4. In quiz list:
   - Click **Publish**.
   - Use **Extend** with a later deadline.
5. Expected:
   - Status changes to Published.
   - Deadline updates.

### D. Create Assignment + Publish + Extend

1. Click **Assignments**.
2. Click **Create assignment**.
3. Fill subject, title, description, deadline and submit.
4. In assignment list:
   - Click **Publish**.
   - Use **Extend** with a later deadline.
5. Expected:
   - Status changes to Published.
   - Deadline updates.

### E. Grade Submissions

1. In assignment card click **Submissions & grade**.
2. For a student submission:
   - Enter marks (0-100)
   - Add feedback
   - Click **Save grade**
3. Expected:
   - Submission status becomes graded.
   - Marks/feedback persist.

### F. Performance Report

1. Click **Performance**.
2. Select subject from dropdown.
3. Verify:
   - Quiz average score
   - Assignment average marks
4. Expected:
   - Values display and update per subject.

### G. Logout

1. Click Logout.

---

## 5) Student Flow (End-to-End)

### A. Login + Dashboard

1. Login as student (`student1`).
2. You should land on `/student/dashboard`.
3. Verify:
   - Upcoming quizzes
   - Pending assignments
   - Quick links

### B. Attempt Quiz (Auto-Marking)

1. Click **Quizzes**.
2. For an open quiz, click **Attempt**.
3. Select answers and click **Submit answers**.
4. Expected:
   - Redirect to quiz list with success message.
   - Score appears (e.g., `Score X/Y`).
   - Attempt cannot be repeated.

### C. Submit Assignment

1. Click **Assignments**.
2. Click an assignment title to open details page.
3. Upload file and submit.
4. Expected:
   - Submission status updates.
   - Submission timestamp appears.
   - Download link shown for submitted file.

### D. View Results

1. Click **Results**.
2. Verify:
   - Quiz results list with scores.
   - Assignment statuses/marks/feedback.
3. Expected:
   - Only this student's data is visible.

### E. Logout

1. Click Logout.

---

## 6) System Rule Validation

## A. Auto-quiz marking

1. Student submits quiz.
2. Immediately check student quiz results.
3. Expected:
   - Score is computed automatically without teacher grading.

## B. Auto-zero for missed assignment deadline

1. Create/publish assignment with a near deadline (teacher).
2. Do not submit as student.
3. After deadline, open student results or teacher report.
4. Expected:
   - Submission status becomes `auto_zero`.
   - Marks are `0`.

---

## 7) Access Control (Important)

Run these quick negative checks:

1. Login as student and manually open `/admin/qams` -> should be denied.
2. Login as teacher and open `/student/results` -> should be denied.
3. Login as admin and open `/teacher/dashboard` -> should be denied.

Expected: role-protected access works.

---

## 8) Quick Smoke Checklist

- [ ] Login works for all 3 roles
- [ ] Admin can manage classes/subjects/teachers/students
- [ ] Teacher can create and publish quizzes/assignments
- [ ] Student can attempt quizzes and submit assignments
- [ ] Teacher can grade submissions
- [ ] Student can see quiz + assignment results
- [ ] Auto-zero + auto-marking rules are working
- [ ] Unauthorized role access is blocked

