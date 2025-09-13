<?php
// Password reset utility
require_once '../../core/includes/config.php';

echo "<h2>Password Reset Utility</h2>";

// Check if reset is requested
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['reset'])) {
    $email = trim($_POST['email']);
    $new_password = $_POST['password'] ?: 'password';
    
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    echo "<h3>Resetting Password</h3>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>";
    echo "<p><strong>New Password:</strong> " . htmlspecialchars($new_password) . "</p>";
    echo "<p><strong>New Hash:</strong> " . substr($hashed_password, 0, 50) . "...</p>";
    
    // Update the password in database
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
        
        if (mysqli_stmt_execute($stmt)) {
            $affected_rows = mysqli_stmt_affected_rows($stmt);
            
            if ($affected_rows > 0) {
                echo "<p style='color: green;'>✅ Password updated successfully!</p>";
                echo "<p><strong>You can now login with:</strong></p>";
                echo "<ul>";
                echo "<li>Email: $email</li>";
                echo "<li>Password: $new_password</li>";
                echo "</ul>";
                
                // Verify the update worked
                echo "<h4>Verification Test:</h4>";
                if (password_verify($new_password, $hashed_password)) {
                    echo "<p style='color: green;'>✅ Password verification test passed</p>";
                } else {
                    echo "<p style='color: red;'>❌ Password verification test failed</p>";
                }
                
            } else {
                echo "<p style='color: red;'>❌ No user found with email: $email</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ Failed to update password: " . mysqli_stmt_error($stmt) . "</p>";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "<p style='color: red;'>❌ Failed to prepare statement: " . mysqli_error($conn) . "</p>";
    }
}

// Check if bulk reset is requested
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bulk_reset'])) {
    echo "<h3>Bulk Password Reset</h3>";
    
    $default_password = 'password';
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    
    // Update all users with the same password
    $sql = "UPDATE users SET password = ?";
    
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            $affected_rows = mysqli_stmt_affected_rows($stmt);
            echo "<p style='color: green;'>✅ Updated passwords for $affected_rows users</p>";
            echo "<p><strong>All users now have password: $default_password</strong></p>";
        } else {
            echo "<p style='color: red;'>❌ Failed to update passwords: " . mysqli_stmt_error($stmt) . "</p>";
        }
        
        mysqli_stmt_close($stmt);
    } else {
        echo "<p style='color: red;'>❌ Failed to prepare statement: " . mysqli_error($conn) . "</p>";
    }
}
?>

<style>
.container { max-width: 800px; margin: 50px auto; padding: 20px; }
.form-group { margin-bottom: 15px; }
.btn { padding: 10px 20px; margin: 5px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
.btn.danger { background: #dc3545; }
.btn:hover { opacity: 0.8; }
input, select { padding: 8px; border: 1px solid #ddd; border-radius: 5px; width: 100%; max-width: 300px; }
.user-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; }
</style>

<div class="container">
    <p>This utility helps reset user passwords to enable login testing.</p>
    
    <!-- Current Users -->
    <div class="user-list">
        <h4>Current Users in Database:</h4>
        <?php
        if (isset($conn) && $conn) {
            $sql = "SELECT id, username, email, role FROM users ORDER BY role, username";
            $result = mysqli_query($conn, $sql);
            
            if ($result && mysqli_num_rows($result) > 0) {
                echo "<table border='1' cellpadding='8' cellspacing='0' style='width: 100%;'>";
                echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
                
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No users found in database.</p>";
            }
        } else {
            echo "<p style='color: red;'>Database connection failed.</p>";
        }
        ?>
    </div>
    
    <!-- Individual Reset -->
    <h3>Reset Individual User Password</h3>
    <form method="post">
        <div class="form-group">
            <label>User Email:</label><br>
            <input type="email" name="email" placeholder="admin@test.com" required>
        </div>
        
        <div class="form-group">
            <label>New Password (leave blank for 'password'):</label><br>
            <input type="text" name="password" placeholder="password">
        </div>
        
        <button type="submit" name="reset" class="btn">Reset Password</button>
    </form>
    
    <hr>
    
    <!-- Bulk Reset -->
    <h3>Reset All User Passwords</h3>
    <p>This will set ALL users' passwords to "password" for testing purposes.</p>
    <form method="post" onsubmit="return confirm('Are you sure you want to reset ALL user passwords?')">
        <button type="submit" name="bulk_reset" class="btn danger">Reset All Passwords to 'password'</button>
    </form>
    
    <hr>
    
    <h3>Quick Test Links</h3>
    <p>After resetting passwords, use these links to test:</p>
    <ul>
        <li><a href="../../public/login.php" target="_blank">Login Page</a></li>
        <li><a href="debug_login.php" target="_blank">Debug Login Test</a></li>
        <li><a href="test_login.php" target="_blank">Password Hash Test</a></li>
    </ul>
</div>
