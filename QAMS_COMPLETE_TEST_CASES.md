# QAMS Complete Test Cases

> Template format requested by user. Update placeholders (`-----`) before execution where needed.

---

## TC-001
Field | Value
---|---
Test Case Title      | Login with valid admin credentials |
Test Case Id         | TC-001 |
Actor                | Admin |
Description          | Verify admin can login and reach admin dashboard. |
Actions              | Open `/login` -> Enter valid admin username/password -> Click Login |
Pre-condition        | Admin account exists and is active. |
Post-Conditions      | Admin session is created; redirected to `/admin/dashboard`. |
Alternative Path     | If credentials invalid, show validation error and stay on login page. |
Tested by            | ----- |
Exceptions           | DB/server unavailable. |
Result               | ----- |

## TC-002
Field | Value
---|---
Test Case Title      | Login blocked account denied |
Test Case Id         | TC-002 |
Actor                | Teacher/Student |
Description          | Verify blocked user cannot authenticate. |
Actions              | Set `users.active=0` for target user -> Attempt login with correct password |
Pre-condition        | User exists and status is blocked. |
Post-Conditions      | No session created; blocked message displayed. |
Alternative Path     | If user unblocked (`active=1`), login succeeds. |
Tested by            | ----- |
Exceptions           | None |
Result               | ----- |

## TC-003
Field | Value
---|---
Test Case Title      | Role-based dashboard redirect |
Test Case Id         | TC-003 |
Actor                | Admin/Teacher/Student |
Description          | Verify each role lands on correct dashboard after login. |
Actions              | Login as each role one-by-one |
Pre-condition        | Active users for all roles exist. |
Post-Conditions      | Admin->`/admin/dashboard`, Teacher->`/teacher/dashboard`, Student->`/student/dashboard`. |
Alternative Path     | If role is changed by admin, redirect updates accordingly. |
Tested by            | ----- |
Exceptions           | Session corruption. |
Result               | ----- |

## TC-004
Field | Value
---|---
Test Case Title      | Unauthorized route access blocked |
Test Case Id         | TC-004 |
Actor                | Teacher/Student |
Description          | Verify non-admin users cannot open admin QAMS routes. |
Actions              | Login as teacher/student -> Open `/admin/qams` and `/admin/qams/reports` |
Pre-condition        | User logged in with non-admin role. |
Post-Conditions      | Access denied (403 or redirect per middleware behavior). |
Alternative Path     | Admin role can access successfully. |
Tested by            | ----- |
Exceptions           | Custom middleware override. |
Result               | ----- |

## TC-005
Field | Value
---|---
Test Case Title      | Admin create class |
Test Case Id         | TC-005 |
Actor                | Admin |
Description          | Verify admin can create school class. |
Actions              | Go to `/admin/qams/classes` -> Enter class name -> Submit |
Pre-condition        | Admin logged in. |
Post-Conditions      | New row inserted in `school_classes`; success message shown. |
Alternative Path     | Duplicate name should fail validation. |
Tested by            | ----- |
Exceptions           | Unique index missing. |
Result               | ----- |

## TC-006
Field | Value
---|---
Test Case Title      | Admin create subject under class |
Test Case Id         | TC-006 |
Actor                | Admin |
Description          | Verify subject is created against selected class. |
Actions              | Open `/admin/qams/subjects` -> Select class -> Enter subject name -> Submit |
Pre-condition        | At least one class exists. |
Post-Conditions      | Subject saved in `subjects` with correct `school_class_id`. |
Alternative Path     | Invalid class id returns validation error. |
Tested by            | ----- |
Exceptions           | FK constraint disabled. |
Result               | ----- |

## TC-007
Field | Value
---|---
Test Case Title      | Admin register teacher |
Test Case Id         | TC-007 |
Actor                | Admin |
Description          | Verify teacher user and profile are created together. |
Actions              | Go to `/admin/qams/teachers/create` -> Fill teacher form -> Submit |
Pre-condition        | Admin logged in; username not already used. |
Post-Conditions      | `users` row with role=teacher + `teacher_profiles` row created. |
Alternative Path     | Duplicate username fails validation. |
Tested by            | ----- |
Exceptions           | Transaction rollback on partial failure. |
Result               | ----- |

## TC-008
Field | Value
---|---
Test Case Title      | Admin register student with enrollment |
Test Case Id         | TC-008 |
Actor                | Admin |
Description          | Verify student user/profile created and subject enrollment assigned. |
Actions              | Open `/admin/qams/students/create` -> Fill details including class and subjects -> Submit |
Pre-condition        | Class and subjects already exist. |
Post-Conditions      | `users` role=student + `student_profiles` + `student_subject` rows created. |
Alternative Path     | Duplicate admission number fails validation. |
Tested by            | ----- |
Exceptions           | Image upload storage failure. |
Result               | ----- |

## TC-009
Field | Value
---|---
Test Case Title      | Admin assign teacher to subject |
Test Case Id         | TC-009 |
Actor                | Admin |
Description          | Verify teacher-subject mapping is stored in pivot table. |
Actions              | On subjects page choose teacher for subject -> Click Assign |
Pre-condition        | Teacher and subject exist. |
Post-Conditions      | Row added to `subject_teacher`; teacher appears in subject teacher list. |
Alternative Path     | Non-teacher user selected -> validation error. |
Tested by            | ----- |
Exceptions           | Duplicate assign attempts (should not create duplicates). |
Result               | ----- |

## TC-010
Field | Value
---|---
Test Case Title      | Admin block/unblock user |
Test Case Id         | TC-010 |
Actor                | Admin |
Description          | Verify admin can toggle user status except own account. |
Actions              | From `/admin/dashboard` click Block/Unblock on target user |
Pre-condition        | Target user exists and is not current admin. |
Post-Conditions      | `users.active` toggles between `1` and `0`. |
Alternative Path     | Attempt block self should show error. |
Tested by            | ----- |
Exceptions           | Concurrent updates by multiple admins. |
Result               | ----- |

## TC-011
Field | Value
---|---
Test Case Title      | Admin search users by name/username |
Test Case Id         | TC-011 |
Actor                | Admin |
Description          | Verify search returns matching users across all roles. |
Actions              | Enter search text in admin dashboard search and submit |
Pre-condition        | Multiple users with different roles exist. |
Post-Conditions      | Matched users shown, including teacher/admin/student where relevant. |
Alternative Path     | Empty search returns paginated full list. |
Tested by            | ----- |
Exceptions           | Collation/case-sensitivity issues. |
Result               | ----- |

## TC-012
Field | Value
---|---
Test Case Title      | Teacher add question bank item |
Test Case Id         | TC-012 |
Actor                | Teacher |
Description          | Verify teacher can add MCQ question for assigned subject. |
Actions              | Go `/teacher/question-bank` -> Fill question/options/correct option -> Submit |
Pre-condition        | Teacher is assigned to selected subject. |
Post-Conditions      | New `question_bank_items` row created with `teacher_id` and `subject_id`. |
Alternative Path     | Unassigned subject returns 403. |
Tested by            | ----- |
Exceptions           | Invalid option key outside A/B/C/D. |
Result               | ----- |

## TC-013
Field | Value
---|---
Test Case Title      | Teacher create quiz draft |
Test Case Id         | TC-013 |
Actor                | Teacher |
Description          | Verify quiz is created as draft with valid dates. |
Actions              | Go `/teacher/quizzes/create` -> Fill form -> Submit |
Pre-condition        | Assigned subject exists; deadline after start time. |
Post-Conditions      | New `quizzes` record with `published=false`. |
Alternative Path     | Deadline before start should fail validation. |
Tested by            | ----- |
Exceptions           | Timezone mismatch. |
Result               | ----- |

## TC-014
Field | Value
---|---
Test Case Title      | Teacher publish quiz |
Test Case Id         | TC-014 |
Actor                | Teacher |
Description          | Verify teacher can publish own quiz. |
Actions              | From quizzes list click Publish on own quiz |
Pre-condition        | Quiz belongs to logged-in teacher. |
Post-Conditions      | `quizzes.published=true`; visible to enrolled students. |
Alternative Path     | Publishing another teacher quiz returns 403. |
Tested by            | ----- |
Exceptions           | Stale UI cache. |
Result               | ----- |

## TC-015
Field | Value
---|---
Test Case Title      | Teacher extend quiz deadline |
Test Case Id         | TC-015 |
Actor                | Teacher |
Description          | Verify deadline extension is persisted. |
Actions              | Click Extend on quiz -> Provide later deadline -> Submit |
Pre-condition        | Teacher owns quiz; valid datetime provided. |
Post-Conditions      | `quizzes.deadline` updated. |
Alternative Path     | Invalid datetime or before start returns validation error. |
Tested by            | ----- |
Exceptions           | Quiz already expired at submission instant. |
Result               | ----- |

## TC-016
Field | Value
---|---
Test Case Title      | Teacher create assignment draft |
Test Case Id         | TC-016 |
Actor                | Teacher |
Description          | Verify assignment creation and default submission rows for enrolled students. |
Actions              | Go `/teacher/assignments/create` -> Fill form -> Submit |
Pre-condition        | Assigned subject with enrolled students exists. |
Post-Conditions      | Assignment saved unpublished; `assignment_submissions` created per enrolled student with pending status. |
Alternative Path     | No students enrolled -> assignment created with zero submission rows. |
Tested by            | ----- |
Exceptions           | Transaction failure during bulk creation. |
Result               | ----- |

## TC-017
Field | Value
---|---
Test Case Title      | Teacher publish and extend assignment |
Test Case Id         | TC-017 |
Actor                | Teacher |
Description          | Verify assignment publish and deadline extension. |
Actions              | From assignment list click Publish -> then Extend deadline |
Pre-condition        | Assignment belongs to teacher. |
Post-Conditions      | `published=true`; `deadline` updated. |
Alternative Path     | Another teacher attempting action gets 403. |
Tested by            | ----- |
Exceptions           | Invalid deadline format. |
Result               | ----- |

## TC-018
Field | Value
---|---
Test Case Title      | Teacher grade submission |
Test Case Id         | TC-018 |
Actor                | Teacher |
Description          | Verify teacher can assign marks and feedback. |
Actions              | Open `/teacher/assignments/{id}/submissions` -> Enter marks+feedback -> Save |
Pre-condition        | Submission exists for teacher's assignment. |
Post-Conditions      | `assignment_submissions` updated with marks, feedback, status=graded, graded_by=teacher. |
Alternative Path     | Marks outside 0-100 fail validation. |
Tested by            | ----- |
Exceptions           | Simultaneous grading edits. |
Result               | ----- |

## TC-019
Field | Value
---|---
Test Case Title      | Teacher performance report |
Test Case Id         | TC-019 |
Actor                | Teacher |
Description          | Verify subject-wise averages for quiz and assignment are generated. |
Actions              | Open `/teacher/reports/performance` -> Select assigned subject |
Pre-condition        | Teacher has assigned subject and at least some attempts/submissions. |
Post-Conditions      | Report shows `quiz_average`, `assignment_average`, `generated_at`. |
Alternative Path     | Unassigned subject request returns 403. |
Tested by            | ----- |
Exceptions           | Null averages when no data (should still render safely). |
Result               | ----- |

## TC-020
Field | Value
---|---
Test Case Title      | Student view available quizzes |
Test Case Id         | TC-020 |
Actor                | Student |
Description          | Verify student sees only published quizzes for enrolled subjects. |
Actions              | Login student -> Open `/student/quizzes` |
Pre-condition        | Student enrolled in one or more subjects. |
Post-Conditions      | List contains only published quizzes for enrolled subjects. |
Alternative Path     | Unpublished/unenrolled subject quizzes are not shown. |
Tested by            | ----- |
Exceptions           | Enrollment cache inconsistency. |
Result               | ----- |

## TC-021
Field | Value
---|---
Test Case Title      | Student open quiz attempt form |
Test Case Id         | TC-021 |
Actor                | Student |
Description          | Verify attempt form opens only when quiz is allowed. |
Actions              | Open `/student/quizzes/{quiz}/attempt` |
Pre-condition        | Quiz is published, student enrolled, deadline not passed, no previous attempt. |
Post-Conditions      | Attempt page shows list of questions. |
Alternative Path     | Any violated condition returns 403. |
Tested by            | ----- |
Exceptions           | Clock skew around exact deadline. |
Result               | ----- |

## TC-022
Field | Value
---|---
Test Case Title      | Student submit quiz (auto-marking) |
Test Case Id         | TC-022 |
Actor                | Student |
Description          | Verify score is auto-calculated and answers saved atomically. |
Actions              | Submit answers to `/student/qams/quizzes/{quiz}/attempt` |
Pre-condition        | Valid question IDs from same subject and valid options. |
Post-Conditions      | `quiz_attempts` created with score; `quiz_answers` rows created with `is_correct`. |
Alternative Path     | Re-attempt or invalid questions returns 422. |
Tested by            | ----- |
Exceptions           | Transaction rollback on mid-loop failure. |
Result               | ----- |

## TC-023
Field | Value
---|---
Test Case Title      | Student cannot attempt quiz twice |
Test Case Id         | TC-023 |
Actor                | Student |
Description          | Verify one-attempt-only constraint per quiz/student. |
Actions              | Submit quiz once -> Submit again |
Pre-condition        | First attempt already recorded. |
Post-Conditions      | Second request rejected; no duplicate attempt created. |
Alternative Path     | Different student can attempt same quiz. |
Tested by            | ----- |
Exceptions           | Race condition near parallel requests. |
Result               | ----- |

## TC-024
Field | Value
---|---
Test Case Title      | Student submit assignment before deadline |
Test Case Id         | TC-024 |
Actor                | Student |
Description          | Verify timely submission stores file path and pending status. |
Actions              | Open assignment page -> Upload file -> Submit |
Pre-condition        | Assignment published and deadline not passed. |
Post-Conditions      | Submission updated with file path, submitted_at, status=pending, marks=0. |
Alternative Path     | API-style `file_path` string submission accepted when file upload absent. |
Tested by            | ----- |
Exceptions           | File storage disk full/unavailable. |
Result               | ----- |

## TC-025
Field | Value
---|---
Test Case Title      | Student submit assignment after deadline |
Test Case Id         | TC-025 |
Actor                | Student |
Description          | Verify late submission gets auto-zero status immediately. |
Actions              | Submit assignment after deadline |
Pre-condition        | Assignment published; current time > deadline. |
Post-Conditions      | Submission saved with `status=auto_zero`, `marks=0`, auto-zero feedback text. |
Alternative Path     | Before deadline should remain pending. |
Tested by            | ----- |
Exceptions           | Time sync issue between app and DB server. |
Result               | ----- |

## TC-026
Field | Value
---|---
Test Case Title      | Student view results |
Test Case Id         | TC-026 |
Actor                | Student |
Description          | Verify student sees only own quiz attempts and assignment results. |
Actions              | Open `/student/results` |
Pre-condition        | Student has at least one quiz attempt or assignment submission. |
Post-Conditions      | Results page shows only authenticated student's records. |
Alternative Path     | API response returns structured quiz_results and assignment_results. |
Tested by            | ----- |
Exceptions           | Orphaned records with wrong student_id. |
Result               | ----- |

## TC-027
Field | Value
---|---
Test Case Title      | Auto-zero background rule |
Test Case Id         | TC-027 |
Actor                | System |
Description          | Verify pending overdue submissions become auto_zero. |
Actions              | Create pending submission without valid on-time submit -> Trigger request hitting `applyAutoZeroMarks()` |
Pre-condition        | Assignment deadline is in past; submission status=pending. |
Post-Conditions      | Submission updated to `status=auto_zero` and `marks=0`. |
Alternative Path     | Graded submissions remain unchanged. |
Tested by            | ----- |
Exceptions           | Job/request never triggers auto-zero helper. |
Result               | ----- |

## TC-028
Field | Value
---|---
Test Case Title      | Auto-mark accuracy correctness |
Test Case Id         | TC-028 |
Actor                | System |
Description          | Verify calculated quiz score equals count of correct options. |
Actions              | Submit mixed correct/incorrect answers -> Compare score with expected |
Pre-condition        | Quiz questions and answer key exist. |
Post-Conditions      | Stored `quiz_attempts.score` matches computed correct count. |
Alternative Path     | All-correct returns full score; all-wrong returns zero. |
Tested by            | ----- |
Exceptions           | Answer payload duplicates same question id. |
Result               | ----- |

## TC-029
Field | Value
---|---
Test Case Title      | Guest access redirection |
Test Case Id         | TC-029 |
Actor                | Guest |
Description          | Verify unauthenticated users are redirected from protected routes. |
Actions              | Open `/dashboard`, `/teacher/dashboard`, `/student/dashboard`, `/admin/dashboard` without login |
Pre-condition        | No active session. |
Post-Conditions      | Redirected to `/login`. |
Alternative Path     | Authenticated user accesses according to role. |
Tested by            | ----- |
Exceptions           | Route cache stale. |
Result               | ----- |

## TC-030
Field | Value
---|---
Test Case Title      | Logout invalidates session |
Test Case Id         | TC-030 |
Actor                | Any authenticated user |
Description          | Verify logout destroys active session securely. |
Actions              | Login -> POST `/logout` -> Try opening protected page |
Pre-condition        | User is authenticated. |
Post-Conditions      | Session invalidated; redirected to login; protected routes no longer accessible. |
Alternative Path     | Multiple tabs should all become unauthenticated after logout. |
Tested by            | ----- |
Exceptions           | Session driver misconfiguration. |
Result               | ----- |

---

## Notes
- Suggested execution order: TC-001 -> TC-011 (Admin setup), then TC-012 -> TC-019 (Teacher), then TC-020 -> TC-026 (Student), then TC-027 -> TC-030 (System/security).
- Replace `Result` with `Pass`/`Fail` and capture defect ID for failures.
- Replace `Tested by` with tester name and date.
