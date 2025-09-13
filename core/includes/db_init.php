<?php
// Include config file
require_once 'config.php';

// SQL to create users table
$sql_users = "CREATE TABLE IF NOT EXISTS users (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    dob DATE,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// SQL to create student_profiles table
$sql_student_profiles = "CREATE TABLE IF NOT EXISTS student_profiles (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    division VARCHAR(50),
    roll_no VARCHAR(20),
    course VARCHAR(100),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// SQL to create teacher_profiles table
$sql_teacher_profiles = "CREATE TABLE IF NOT EXISTS teacher_profiles (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    department VARCHAR(100),
    subjects TEXT,
    experience INT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// SQL to create survey_questions table
$sql_survey_questions = "CREATE TABLE IF NOT EXISTS survey_questions (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    question TEXT NOT NULL,
    target_role ENUM('student', 'teacher') NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

// SQL to create survey_responses table
$sql_survey_responses = "CREATE TABLE IF NOT EXISTS survey_responses (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    question_id INT NOT NULL,
    rating ENUM('bad', 'neutral', 'good') NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE
)";

// SQL to create teacher_ratings table
$sql_teacher_ratings = "CREATE TABLE IF NOT EXISTS teacher_ratings (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    teacher_id INT NOT NULL,
    rating ENUM('bad', 'neutral', 'good') NOT NULL,
    comment TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
)";

// SQL to create suggestions_complaints table
$sql_suggestions_complaints = "CREATE TABLE IF NOT EXISTS suggestions_complaints (
    id INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('suggestion', 'complaint') NOT NULL,
    content TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";

// Execute the queries
if (mysqli_query($conn, $sql_users)) {
    echo "Table 'users' created successfully.<br>";
} else {
    echo "Error creating table 'users': " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_student_profiles)) {
    echo "Table 'student_profiles' created successfully.<br>";
} else {
    echo "Error creating table 'student_profiles': " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_teacher_profiles)) {
    echo "Table 'teacher_profiles' created successfully.<br>";
} else {
    echo "Error creating table 'teacher_profiles': " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_survey_questions)) {
    echo "Table 'survey_questions' created successfully.<br>";
} else {
    echo "Error creating table 'survey_questions': " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_survey_responses)) {
    echo "Table 'survey_responses' created successfully.<br>";
} else {
    echo "Error creating table 'survey_responses': " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_teacher_ratings)) {
    echo "Table 'teacher_ratings' created successfully.<br>";
} else {
    echo "Error creating table 'teacher_ratings': " . mysqli_error($conn) . "<br>";
}

if (mysqli_query($conn, $sql_suggestions_complaints)) {
    echo "Table 'suggestions_complaints' created successfully.<br>";
} else {
    echo "Error creating table 'suggestions_complaints': " . mysqli_error($conn) . "<br>";
}

// Insert default admin user
$default_admin_password = password_hash("admin123", PASSWORD_DEFAULT);
$insert_admin = "INSERT INTO users (username, email, password, role) 
                VALUES ('Admin', 'admin@college.edu', '$default_admin_password', 'admin')
                ON DUPLICATE KEY UPDATE id=id";

if (mysqli_query($conn, $insert_admin)) {
    echo "Default admin user created successfully.<br>";
} else {
    echo "Error creating default admin user: " . mysqli_error($conn) . "<br>";
}

// Insert some default survey questions for students
$student_questions = [
    "How would you rate the teaching quality in your course?",
    "How satisfied are you with the course materials provided?",
    "How would you rate the classroom facilities?",
    "How would you rate the support provided by faculty?",
    "How satisfied are you with the practical/lab sessions?"
];

foreach ($student_questions as $question) {
    $sql = "INSERT INTO survey_questions (question, target_role) 
            VALUES ('$question', 'student')
            ON DUPLICATE KEY UPDATE question=question";
    mysqli_query($conn, $sql);
}

// Insert some default survey questions for teachers
$teacher_questions = [
    "How would you rate the administrative support provided to faculty?",
    "How satisfied are you with the teaching resources available?",
    "How would you rate the work-life balance at the institution?",
    "How would you rate the professional development opportunities?",
    "How satisfied are you with the student engagement in your classes?"
];

foreach ($teacher_questions as $question) {
    $sql = "INSERT INTO survey_questions (question, target_role) 
            VALUES ('$question', 'teacher')
            ON DUPLICATE KEY UPDATE question=question";
    mysqli_query($conn, $sql);
}

echo "<br>Database setup completed successfully!";
// Close connection
closeConnection($conn);
?>
