# Student Absence Management

## Project Structure:
This project manages student attendance and absences  
It includes separate modules for admins and teachers, with a clear API and frontend structure.

---

## Folder Structure Overview

### 1. api/
Contains all backend PHP API endpoints used to interact with the database. Using PHP and postgreSQL

#### api/admin/
APIs for administrative actions like managing groups, modules, and users.

- groups/ → CRUD operations for student groups  
  - create_group.php  
  - delete_group.php  
  - get_all_groups.php  
  - update_group.php
- modules/ → CRUD operations for course modules  
  - create_module.php  
  - delete_module.php  
  - get_all_modules.php  
  - update_module.php
- users/ → Manage students and teachers  
  - Create, Update, Delete, and Fetch operations for students and teachers  
  - reports.php provides summarized data for the admin dashboard.

#### api/auth/
Handles user authentication and session management.  
- login.php → User login  
- logout.php → User logout  
- check_session.php → Validate active user session

#### api/teacher/
APIs for teachers to manage attendance and class sessions.  
- dashboard_data.php → Fetch teacher’s dashboard summaries  
- get_my_classes.php → Retrieve the teacher’s assigned groups and modules  
- get_students.php → Get students in a selected group  
- save_attendance.php → Record or update student attendance status  

#### api/config/
Contains database configuration files.  
- db.php → Manages the database connection

---

### 2. public/
Contains frontend files (HTML, CSS, and JavaScript) for the web interface.  
Separated by user role (admin and teacher).

#### public/admin/
Interface for administrators to manage groups, modules, students, and teachers.

- js/ → Contains JavaScript logic for data fetching and page interactions  
  Example:  
  - students.js → Manage student data and API calls  
  - modules.js → Handle module operations  
  - reports.js → Display attendance reports  
- HTML pages:  
  - dashboard.html  
  - groups.html  
  - modules.html  
  - teachers.html  
  - reports.html  
- styles/ → Admin CSS styles  
  - dashboard.css, students.css, reports.css, etc.

#### public/teacher/
Frontend pages specifically for teachers.  
- js/ → Handles in-page logic and API communication  
  - dashboard.js → Displays teacher statistics  
  - my_classes.js → Loads the teacher’s classes  
  - take_attendance.js → Manages attendance taking  
- HTML pages:  
  - dashboard.html  
  - my_classes.html  
  - take_attendance.html  
  - history.html

---

### Utility Files
- .env → Stores environment variables (e.g., DB credentials, API URLs)  
- .gitignore → Excludes unnecessary files from version control

---

## ERD Diagram :
<img width="1267" height="870" alt="image" src="https://github.com/user-attachments/assets/31c37ed0-8ed5-4503-b0ac-59c9c65b5881" />


## SQL:

CREATE TABLE users (
    user_id SERIAL PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash TEXT NOT NULL,
    role VARCHAR(50) NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE groups (
    group_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE modules (
    module_id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    has_td BOOLEAN DEFAULT FALSE,
    has_tp BOOLEAN DEFAULT FALSE
);

CREATE TABLE teachers (
    teacher_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    matricule VARCHAR(64) UNIQUE NOT NULL,
    group_id INTEGER,
    module_id INTEGER,
    session_type VARCHAR(10),
    CONSTRAINT fk_teacher_user FOREIGN KEY (user_id)
        REFERENCES users (user_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_teacher_group FOREIGN KEY (group_id)
        REFERENCES groups (group_id)
        ON DELETE SET NULL,
    CONSTRAINT fk_teacher_module FOREIGN KEY (module_id)
        REFERENCES modules (module_id)
        ON DELETE SET NULL
);

CREATE TABLE students (
    student_id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    matricule VARCHAR(64) UNIQUE NOT NULL,
    group_id INTEGER,
    CONSTRAINT fk_student_user FOREIGN KEY (user_id)
        REFERENCES users (user_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_student_group FOREIGN KEY (group_id)
        REFERENCES groups (group_id)
        ON DELETE SET NULL
);

CREATE TABLE sessions (
    session_id SERIAL PRIMARY KEY,
    module_id INTEGER NOT NULL,
    group_id INTEGER NOT NULL,
    teacher_id INTEGER NOT NULL,
    session_date DATE NOT NULL,
    start_time TIME WITHOUT TIME ZONE,
    end_time TIME WITHOUT TIME ZONE,
    CONSTRAINT fk_session_module FOREIGN KEY (module_id)
        REFERENCES modules (module_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_session_group FOREIGN KEY (group_id)
        REFERENCES groups (group_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_session_teacher FOREIGN KEY (teacher_id)
        REFERENCES teachers (teacher_id)
        ON DELETE CASCADE
);

CREATE TABLE attendance (
    attendance_id SERIAL PRIMARY KEY,
    session_id INTEGER NOT NULL,
    student_id INTEGER NOT NULL,
    status VARCHAR(50) NOT NULL,
    CONSTRAINT fk_attendance_session FOREIGN KEY (session_id)
        REFERENCES sessions (session_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_attendance_student FOREIGN KEY (student_id)
        REFERENCES students (student_id)
        ON DELETE CASCADE
);

CREATE TABLE justifications (
    justification_id SERIAL PRIMARY KEY,
    student_id INTEGER NOT NULL,
    attendance_id INTEGER NOT NULL,
    reason TEXT,
    attachment_url TEXT,
    status VARCHAR(50) DEFAULT 'pending',
    submitted_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_justification_student FOREIGN KEY (student_id)
        REFERENCES students (student_id)
        ON DELETE CASCADE,
    CONSTRAINT fk_justification_attendance FOREIGN KEY (attendance_id)
        REFERENCES attendance (attendance_id)
        ON DELETE CASCADE
);

CREATE INDEX idx_user_role ON users (role);
CREATE INDEX idx_teacher_module ON teachers (module_id);
CREATE INDEX idx_student_group ON students (group_id);
CREATE INDEX idx_session_date ON sessions (session_date);
CREATE INDEX idx_attendance_status ON attendance (status);

---
## Relation :
users (1,1)  (1,1) teachers  
users (1,1)  (1,1) students  

groups (1,1)  (0,N) teachers  
groups (1,1)  (0,N) students  
groups (1,1)  (0,N) sessions  

modules (1,1)  (0,N) teachers  
modules (1,1)  (0,N) sessions  

teachers (1,1)  (0,N) sessions  

sessions (1,1)  (0,N) attendance  

students (1,1)  (0,N) attendance  
students (1,1)  (0,N) justifications  

attendance (1,1)  (0,1) justifications
