<?php
// Enable error reporting and logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary files
require_once '../../core/includes/config.php';
require_once '../../core/includes/functions.php';

echo "<h2>Direct Login Test</h2>";

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    echo "<h3>Login Attempt</h3>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
    echo "<p><strong>Role:</strong> " . htmlspecialchars($role) . "</p>";
    
    echo "<h4>Step-by-step verification:</h4>";
    
    // Start session
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    
    // Check database connection
    if (!$conn) {
        echo "<p style='color: red;'>❌ Database connection failed: " . mysqli_connect_error() . "</p>";
        exit;
    } else {
        echo "<p style='color: green;'>✅ Database connection successful</p>";
    }
    
    // Prepare statement
    $sql = "SELECT id, username, email, password, role FROM users WHERE email = ?";
    echo "<p><strong>SQL Query:</strong> $sql</p>";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        echo "<p style='color: green;'>✅ Statement preparation successful</p>";
        
        // Bind parameter
        mysqli_stmt_bind_param($stmt, "s", $email);
        
        // Execute
        if (mysqli_stmt_execute($stmt)) {
            echo "<p style='color: green;'>✅ Query execution successful</p>";
            
            mysqli_stmt_store_result($stmt);
            
            if (mysqli_stmt_num_rows($stmt) == 1) {
                echo "<p style='color: green;'>✅ User found in database</p>";
                
                mysqli_stmt_bind_result($stmt, $id, $username, $db_email, $hashed_password, $user_role);
                
                if (mysqli_stmt_fetch($stmt)) {
                    echo "<p><strong>User Details:</strong></p>";
                    echo "<ul>";
                    echo "<li>ID: $id</li>";
                    echo "<li>Username: $username</li>";
                    echo "<li>Email: $db_email</li>";
                    echo "<li>Role: $user_role</li>";
                    echo "<li>Password Hash: " . substr($hashed_password, 0, 30) . "...</li>";
                    echo "</ul>";
                    
                    // Check role match
                    if ($role === $user_role) {
                        echo "<p style='color: green;'>✅ Role matches ($role)</p>";
                        
                        // Verify password
                        echo "<p><strong>Password Verification:</strong></p>";
                        echo "<p>Input password: '$password'</p>";
                        echo "<p>Stored hash: '$hashed_password'</p>";
                        
                        if (password_verify($password, $hashed_password)) {
                            echo "<p style='color: green;'>✅ Password verification successful!</p>";
                            
                            // Set session variables
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["name"] = $username;
                            $_SESSION["email"] = $db_email;
                            $_SESSION["role"] = $user_role;
                            
                            echo "<p style='color: green;'><strong>✅ LOGIN SUCCESSFUL!</strong></p>";
                            echo "<p><strong>Session Variables Set:</strong></p>";
                            echo "<pre>" . print_r($_SESSION, true) . "</pre>";
                            
                            // Show redirect URL
                            $redirect_url = "";
                            switch ($user_role) {
                                case "student":
                                    $redirect_url = "student/dashboard.php";
                                    break;
                                case "teacher":
                                    $redirect_url = "teacher/dashboard.php";
                                    break;
                                case "admin":
                                    $redirect_url = "admin/dashboard.php";
                                    break;
                            }
                            
                            echo "<p><strong>Should redirect to:</strong> <a href='$redirect_url'>$redirect_url</a></p>";
                            
                        } else {
                            echo "<p style='color: red;'>❌ Password verification failed</p>";
                            
                            // Test password_verify with known values
                            echo "<h5>Password Debugging:</h5>";
                            $test_hash = password_hash($password, PASSWORD_DEFAULT);
                            echo "<p>Generated hash for '$password': $test_hash</p>";
                            echo "<p>Verification with new hash: " . (password_verify($password, $test_hash) ? "✅ Success" : "❌ Failed") . "</p>";
                        }
                    } else {
                        echo "<p style='color: red;'>❌ Role mismatch: requested '$role' but user is '$user_role'</p>";
                    }
                }
            } else {
                echo "<p style='color: red;'>❌ No user found with email: $email</p>";
                echo "<p>Number of rows: " . mysqli_stmt_num_rows($stmt) . "</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Query execution failed: " . mysqli_stmt_error($stmt) . "</p>";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "<p style='color: red;'>❌ Statement preparation failed: " . mysqli_error($conn) . "</p>";
    }
}
?>

<style>
.container { max-width: 800px; margin: 50px auto; padding: 20px; }
.form-group { margin-bottom: 20px; }
.btn { padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
.btn:hover { background: #0056b3; }
input, select { padding: 10px; border: 1px solid #ddd; border-radius: 5px; width: 100%; max-width: 300px; }
</style>

<div class="container">
    <h3>Quick Login Test</h3>
    <form method="post">
        <div class="form-group">
            <label>Email:</label><br>
            <input type="email" name="email" value="admin@test.com" required>
        </div>
        
        <div class="form-group">
            <label>Password:</label><br>
            <input type="password" name="password" value="password" required>
        </div>
        
        <div class="form-group">
            <label>Role:</label><br>
            <select name="role" required>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
            </select>
        </div>
        
        <button type="submit" class="btn">Test Login</button>
    </form>
    
    <h4>Available Test Users:</h4>
    <?php
    if (isset($conn) && $conn) {
        $sql = "SELECT username, email, role FROM users LIMIT 10";
        $result = mysqli_query($conn, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            echo "<ul>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<li><strong>" . htmlspecialchars($row['email']) . "</strong> - " . htmlspecialchars($row['role']) . " (" . htmlspecialchars($row['username']) . ")</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No users found</p>";
        }
    }
    ?>
</div>
