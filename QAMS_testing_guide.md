## QAMS Testing Guide (Seeded Data)

This guide explains how to seed the database and test all main flows (Admin, Teacher, Student) using the sample data.

---

### 1. Prepare and Seed the Application

1. **Install dependencies and configure `.env`**  
   Follow the existing `README.md`:
   - `composer install`
   - `npm install`
   - copy `.env.example` → `.env` and configure DB.

2. **Run migrations and seeders**

```bash
php artisan migrate:fresh --seed
```

This will:
- Create all tables (users, classes, subjects, teachers, students, quizzes, questions, assignments, etc.).
- Insert:
  - Admin user: `admin` / `admin123`
  - Teachers: `teacher1` / `teacher123`, `teacher2` / `teacher123`
  - Students: `student1` … `student5` / `student123`
  - Class: `Class 10 A`
  - Subjects: `Mathematics`, `Science` (for Class 10 A)
  - Teacher–subject links (teacher1 → Maths, teacher2 → Science)
  - One published quiz “Maths Quiz 1” with 2 auto-marked questions.
  - One assignment “Algebra Assignment 1` for Mathematics.

3. **Start the servers**

```bash
php artisan serve
npm run dev
```

Open `http://127.0.0.1:8000` in your browser.

---

### 2. Admin Flow (User Management)

1. **Login as Admin**
   - Go to `/login`.
   - Use:
     - Username: `admin`
     - Password: `admin123`

2. **View and manage users**
   - After login, you should be redirected to the admin area.
   - In the admin dashboard:
     - See list of all users (admin, teachers, students).
     - Use **Edit** to change username / role / password.
     - Use **Block / Unblock** to disable or enable an account.

3. **Verify blocking**
   - Block one student (for example `student5`).
   - Log out as admin.
   - Try logging in as `student5` / `student123`:
     - You should see an error message saying the account is blocked.

---

### 3. Teacher Flow (Quizzes and Assignments)

#### 3.1 Login and Dashboard

1. **Login as Teacher**
   - Go to `/login`.
   - Use for example:
     - Username: `teacher1`
     - Password: `teacher123`
   - After login:
     - Visit `/teacher/dashboard`.
     - You should see cards showing quiz and assignment counts.

#### 3.2 Quiz Management and Auto-Marking

1. **View seeded quiz**
   - Navigate to **Quizzes** (link from teacher dashboard).
   - You should see “Maths Quiz 1”.
   - Status: Published, with start and end time.

2. **Create a new quiz (optional)**
   - Click **Create New Quiz**.
   - Fill:
     - Title: e.g. `Maths Quiz 2`.
     - Subject: `Mathematics (Class 10 A)`.
     - Set start and end times.
     - Add questions with options and correct answers.
   - Save the quiz.
   - Optionally click **Publish** to make it visible to students.

3. **View quiz results**
   - For “Maths Quiz 1”, click **Results**.
   - Initially, there may be no attempts. You will test this by logging in as a student later.

#### 3.3 Assignment Management and Auto-Zero

1. **View seeded assignment**
   - Navigate to **Assignments**.
   - You should see “Algebra Assignment 1”.
   - Check assigned date and deadline.

2. **Create a new assignment (optional)**
   - Click **Create New Assignment**.
   - Fill title, subject, deadline, and description.
   - Save and confirm it appears in the list.

3. **View submissions**
   - From the assignment list, click **Submissions** on “Algebra Assignment 1”.
   - You should see:
     - At least one example graded submission for `Student 1` (from seeder).
   - Once students submit from their accounts, additional submissions will appear here.

4. **Test auto-zero**
   - Temporarily change the assignment’s deadline to the past:
     - Click **Edit** on “Algebra Assignment 1”.
     - Set **Deadline** to a time before now and save.
   - Go back to **Submissions** for that assignment.
   - Click **Auto-assign Zero for Missing**.
   - The system will:
     - Create submissions with marks `0` and status `auto_zero` for students who did not submit on time.
   - Verify:
     - In the submissions table, rows for remaining students now show “Auto Zero” with marks `0`.

---

### 4. Student Flow (Quizzes, Assignments, Reports)

#### 4.1 Login and Dashboard

1. **Login as a Student**
   - Go to `/login`.
   - Use for example:
     - Username: `student1`
     - Password: `student123`
   - After login:
     - Visit `/student/dashboard`.
     - You should see:
       - Upcoming quizzes (e.g., “Maths Quiz 1” if within its time window).
       - Pending assignments (e.g., “Algebra Assignment 1”).

#### 4.2 Quiz Flow and Auto-Marking

1. **View available quizzes**
   - Navigate to **Quizzes** from the student dashboard.
   - Confirm “Maths Quiz 1” is listed with status **Open** (if end time not passed).

2. **Attempt the quiz**
   - Click **Attempt** on “Maths Quiz 1”.
   - The quiz page will show:
     - Questions.
     - Options A–D for each question.
   - Select answers for all questions.
   - Click **Submit Quiz**.

3. **Verify automatic marking**
   - After submit, you should be redirected to the quiz list with a success message including your score.
   - The table should now show:
     - Status: `Completed (score)` for “Maths Quiz 1”.
   - As teacher (`teacher1`), check “Maths Quiz 1” → **Results**:
     - You should see `Student 1` with the same score.

#### 4.3 Assignment Flow and Deadlines

1. **View assignments**
   - From the student dashboard, open **Assignments**.
   - Confirm “Algebra Assignment 1” is listed as **Pending** (if deadline not passed).

2. **Submit assignment**
   - Click **View / Submit** on “Algebra Assignment 1”.
   - Page shows:
     - Subject, assigned date, deadline, description.
   - Under **Submit Assignment**:
     - Choose a file (PDF or any allowed format).
     - Click **Upload**.

3. **Check submission status**
   - After upload:
     - The top of the assignment page shows your submission status as `Submitted`.
     - You can see a link to view the uploaded file.
   - In the assignments list:
     - Status should show `Submitted` for that assignment.

4. **Teacher grading and feedback**
   - Log in as `teacher1`.
   - Go to **Assignments** → `Algebra Assignment 1` → **Submissions**.
   - Find `Student 1`, set marks and optional feedback, and click **Save**.
   - Log back in as `student1`, open the same assignment:
     - You should see updated `Marks` and `Feedback`.

#### 4.4 Performance Report

1. **Open performance report**
   - As `student1`, go to **Performance** from the dashboard.
   - You should see:
     - Quiz Results list, including “Maths Quiz 1” with your score.
     - Assignment Results list, including “Algebra Assignment 1” with your marks and status.

---

### 5. Testing Other Users and Edge Cases

- **Other students**
  - Login as `student2`, `student3`, etc. to:
    - Attempt the same quiz and submit assignments.
    - Verify that each student only sees their own results.

- **Blocked accounts**
  - As admin, block `teacher2` or a student.
  - Try to log in using that account and confirm you get the “blocked account” message.

- **Closed quizzes/assignments**
  - Change quiz `ends_at` to a past time, then:
    - Check that students see the quiz as `Closed` and cannot attempt.
  - Change assignment deadlines to past and run auto-zero:
    - Verify that students who did not submit receive `Auto Zero` status.

With these steps you can fully exercise all flows (Admin, Teacher, Student), verify automatic quiz marking, automatic zero marks for missing assignments, and confirm that the UI behaves correctly for each role. 

