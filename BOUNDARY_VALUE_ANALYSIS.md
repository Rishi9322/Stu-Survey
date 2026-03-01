# Boundary Value Analysis (BVA)

Date: 2026-02-02

Scope: All user inputs with server-side handling or explicit client constraints identified in the codebase.

---

## 1) Public Authentication & Registration

### 1.1 Registration (public/register.php)

**Fields and boundaries**

- **Full Name (`name`)** – required, non-empty.
  - Boundary tests: empty, 1 char, typical length.
- **Email (`email`)** – required, non-empty; must be unique in `users` table.
  - Boundary tests: empty, duplicate existing email, valid email format, very long email (max length not enforced).
- **Password (`password`)** – required; minimum length 6.
  - Boundary tests: empty, length 5, length 6, length 7.
- **Confirm Password (`confirm_password`)** – required; must match `password`.
  - Boundary tests: empty, mismatch, exact match.
- **Date of Birth (`dob`)** – required, non-empty.
  - Boundary tests: empty, valid date; (no min/max date enforced).
- **Role (`role`)** – required; expected values: `student`, `teacher`, `admin`.
  - Boundary tests: empty, each valid role, invalid role string.
- **Access Code (`access_code`)** – required only for `teacher` and `admin`; must exist and be valid:
  - Must exist for role
  - Must be active
  - Must not be expired
  - `current_uses < max_uses` (when `max_uses > 0`)
  - Boundary tests: empty (teacher/admin), invalid code, deactivated code, expired code, `current_uses = max_uses - 1`, `current_uses = max_uses`, `current_uses = max_uses + 1`.

**Student role only**

- **Division (`division`)** – required, non-empty.
  - Boundary tests: empty, 1 char, typical length.
- **Roll Number (`roll_no`)** – required, non-empty.
  - Boundary tests: empty, 1 char, typical length.
- **Course (`course`)** – required, non-empty.
  - Boundary tests: empty, valid option.

**Teacher role only**

- **Department (`department`)** – required, non-empty.
  - Boundary tests: empty, 1 char, typical length.
- **Subjects (`subjects`)** – required, non-empty.
  - Boundary tests: empty, 1 char, typical list.
- **Experience (`experience`)** – optional; client UI sets `min=0`, `max=50`.
  - Boundary tests: empty (treated as 0), -1, 0, 1, 49, 50, 51.

**References:**
- Server validation: public/register.php
- Access code validation: core/includes/functions.php

---

### 1.2 Login (public/login.php)

- **Email (`email`)** – required, non-empty.
  - Boundary tests: empty, valid email.
- **Password (`password`)** – required, non-empty.
  - Boundary tests: empty, non-empty.
- **Role (`role`)** – required; expected values: `student`, `teacher`, `admin`.
  - Boundary tests: empty, each valid role, invalid role string.

**Reference:** public/login.php

---

## 2) Profile Management

### 2.1 Student Profile (app/student/profile.php)

- **Name (`name`)** – required, non-empty.
  - Boundary tests: empty, 1 char, typical length.
- **Date of Birth (`dob`)** – required, non-empty.
  - Boundary tests: empty, valid date.
- **Division (`division`)** – required, non-empty.
  - Boundary tests: empty, 1 char.
- **Roll Number (`roll_no`)** – required, non-empty.
  - Boundary tests: empty, 1 char.
- **Course (`course`)** – required, non-empty.
  - Boundary tests: empty, valid option.

**Reference:** app/student/profile.php

### 2.2 Teacher Profile (app/teacher/profile.php)

- **Name (`name`)** – required, non-empty.
  - Boundary tests: empty, 1 char, typical length.
- **Date of Birth (`dob`)** – required, non-empty.
  - Boundary tests: empty, valid date.
- **Department (`department`)** – required, non-empty.
  - Boundary tests: empty, 1 char.
- **Subjects (`subjects`)** – required, non-empty.
  - Boundary tests: empty, 1 char.
- **Experience (`experience`)** – optional; treated as 0 when empty.
  - Boundary tests: empty (0), -1, 0, 1, 49, 50, 51 (UI often enforces 0–50).

**Reference:** app/teacher/profile.php

---

## 3) Surveys

### 3.1 Student Survey (app/student/survey.php)

**Survey questions (`question[question_id]`)**
- Required per question; allowed values: `bad`, `neutral`, `good`.
- Boundary tests: missing answer, each valid choice, invalid value.

**Teacher ratings (`teacher[teacher_id]`)**
- Optional block; allowed values: `bad`, `neutral`, `good`.
- Boundary tests: missing rating, each valid choice, invalid value.

**Teacher comment (`teacher_comment[teacher_id]`)**
- Optional; no length constraints enforced.
- Boundary tests: empty, 1 char, very long input.

**Suggestion (`suggestion`)**
- Optional; no length constraints enforced.
- Boundary tests: empty, 1 char, very long input.

**Complaint (`complaint`)**
- Optional; no length constraints enforced.
- Boundary tests: empty, 1 char, very long input.

**Reference:** app/student/survey.php

### 3.2 Teacher Survey (app/teacher/survey.php)

**Survey questions (`question[question_id]`)**
- Required per question; allowed values: `bad`, `neutral`, `good`.
- Boundary tests: missing answer, each valid choice, invalid value.

**Suggestion (`suggestion`)**
- Optional; no length constraints enforced.
- Boundary tests: empty, 1 char, very long input.

**Complaint (`complaint`)**
- Optional; no length constraints enforced.
- Boundary tests: empty, 1 char, very long input.

**Reference:** app/teacher/survey.php

---

## 4) Anonymous Feedback (Dashboards)

### 4.1 Student Dashboard (app/student/dashboard.php)

- **Feedback Subject (`feedback_subject`)** – required, non-empty.
  - Boundary tests: empty, 1 char, typical length.
- **Feedback Description (`feedback_description`)** – required, non-empty.
  - Boundary tests: empty, 1 char, very long input.
- **Feedback Type (`feedback_type`)** – required.
  - Boundary tests: empty, valid option, invalid value.

**Reference:** app/student/dashboard.php

### 4.2 Teacher Dashboard (app/teacher/dashboard.php)

- **Feedback Subject (`feedback_subject`)** – required, non-empty.
- **Feedback Description (`feedback_description`)** – required, non-empty.
- **Feedback Type (`feedback_type`)** – required.

**Reference:** app/teacher/dashboard.php

---

## 5) Admin Survey Management (app/admin/survey_management.php)

### 5.1 Create Survey
- **Title (`survey_title`)** – required, non-empty.
- **Description (`survey_description`)** – required, non-empty.
- **Target Role (`survey_target_role`)** – required.
  - Boundary tests: empty, valid role, invalid role.

### 5.2 Duplicate Survey
- **Survey ID (`survey_id`)** – required (no validation).
- **New Title (`new_title`)** – used to create title; no validation.
  - Boundary tests: empty, 1 char, very long input.

### 5.3 Update Survey Status
- **Survey ID (`survey_id`)** – required.
- **Status (`status`)** – required; when `status = active`, each question set must have **at least 10 questions**.
  - Boundary tests: `question_count = 9`, `10`, `11` per set.

### 5.4 Restart Survey
- **Survey ID (`survey_id`)** – required.
- **Session Name (`session_name`)** – required (no validation).
  - Boundary tests: empty, 1 char, very long input.

### 5.5 Create Question Set
- **Set Name (`set_name`)** – required, non-empty.
  - Boundary tests: empty, 1 char, typical length.
- **Set Description (`set_description`)** – optional.
- **Display Order (`display_order`)** – integer (no min enforced).
  - Boundary tests: empty, 0, 1, very large number.

### 5.6 Add Question to Set
- **Question Set ID (`question_set_id`)** – required.
- **Question (`question`)** – required, non-empty.
- **Question Type (`question_type`)** – optional; default `rating`.
- **Display Order (`display_order`)** – integer; default `1`.
- **Target Role (`target_role`)** – optional; default `student`.
  - Boundary tests: empty, valid role, invalid role.

### 5.7 Update Survey Settings
- **Survey ID (`survey_id`)** – required.
- **Title (`survey_title`)** – required (no explicit empty check).
- **Target Role (`survey_target_role`)** – required (no explicit empty check).
- **Description (`survey_description`)** – required (no explicit empty check).
- **Start Date (`start_date`)** – optional (nullable).
- **End Date (`end_date`)** – optional (nullable).
- **Anonymous (`is_anonymous`)** – checkbox (0/1).
- **Required (`is_required`)** – checkbox (0/1).
  - Boundary tests: empty vs set for dates, `start_date = end_date`, `end_date < start_date` (not enforced).

### 5.8 Add Standalone Question
- **Question (`question`)** – required, non-empty.
- **Target Role (`target_role`)** – required.

### 5.9 Update Question
- **Question ID (`question_id`)** – required.
- **Question (`question`)** – required, non-empty.
- **Is Active (`is_active`)** – checkbox (0/1).

### 5.10 Delete Question
- **Question ID (`question_id`)** – required.

**Reference:** app/admin/survey_management.php

---

## 6) Training Data API (app/api/training_endpoint.php)

- **Action (`action`)** – required; allowed values: `upload_file`, `import_sheets`, `get_stats`, `preview_data`, `export_data`.
  - Boundary tests: empty, each valid value, invalid value.

**Action-specific inputs**

- **upload_file**: `training_file` – required.
  - Boundary tests: no file, empty file, valid file; very large file (size limits not enforced here).
- **import_sheets**: `sheets_url` – required, non-empty.
  - Boundary tests: empty, valid URL, invalid URL.
- **export_data**: `category` – optional (nullable).
  - Boundary tests: empty/null, valid category, invalid category.

**Reference:** app/api/training_endpoint.php

---

## Notes / Gaps

- Many fields only validate **non-empty**; no length/format constraints are enforced server-side.
- Date ordering (start/end) is not validated in code.
- Numeric ranges (e.g., display order) are not bounded server-side.
- Ratings are string-based; invalid values are not rejected in the PHP layer.

---

## Summary of Minimum Explicit Numeric Boundary

- **Password length**: min 6.
- **Experience**: UI range 0–50.
- **Activation rule**: minimum 10 questions per question set.

