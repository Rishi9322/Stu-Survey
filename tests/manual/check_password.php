<?php
// Simple script to check if the password hash is correct
$password = "password123";
$hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

if (password_verify($password, $hash)) {
    echo "Password verification successful!";
} else {
    echo "Password verification failed!";
    
    // Generate a new hash for reference
    $new_hash = password_hash($password, PASSWORD_DEFAULT);
    echo "\nNew hash for 'password123': " . $new_hash;
}
?>

