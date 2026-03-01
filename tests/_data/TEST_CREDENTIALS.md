# Test User Credentials

## Important: Setup Required Before Running Tests

Before running the test suite, you MUST create test users in your database.

### Test User Accounts

#### 1. Student Account
- **Username**: `student@test.com`
- **Email**: `student@test.com`
- **Password**: `Student123!`
- **Role**: `student`
- **Full Name**: `Test Student`
- **Student ID**: `STU001`

#### 2. Teacher Account
- **Username**: `teacher@test.com`
- **Email**: `teacher@test.com`
- **Password**: `Teacher123!`
- **Role**: `teacher`
- **Full Name**: `Test Teacher`
- **Department**: `Computer Science`
- **Subject**: `Programming`

#### 3. Admin Account
- **Username**: `admin@test.com`
- **Email**: `admin@test.com`
- **Password**: `Admin123!`
- **Role**: `admin`
- **Full Name**: `Test Administrator`

## Quick Setup

### Option 1: Run SQL Script (Recommended)

```bash
# Import test users into your database
mysql -u root student_feedback < tests/_data/test_users.sql

# Or from phpMyAdmin:
# 1. Open phpMyAdmin
# 2. Select 'student_feedback' database
# 3. Go to SQL tab
# 4. Copy and paste contents of tests/_data/test_users.sql
# 5. Click 'Go'
```

### Option 2: Manual Creation

1. Go to your application's registration page
2. Create three accounts with the credentials above
3. Update user roles in the database if needed

### Option 3: PHP Script

```php
<?php
// Create test users programmatically
require_once 'core/config/database.php';

$users = [
    [
        'username' => 'student@test.com',
        'email' => 'student@test.com',
        'password' => password_hash('Student123!', PASSWORD_DEFAULT),
        'role' => 'student',
        'full_name' => 'Test Student',
        'student_id' => 'STU001'
    ],
    [
        'username' => 'teacher@test.com',
        'email' => 'teacher@test.com',
        'password' => password_hash('Teacher123!', PASSWORD_DEFAULT),
        'role' => 'teacher',
        'full_name' => 'Test Teacher',
        'department' => 'Computer Science',
        'subject' => 'Programming'
    ],
    [
        'username' => 'admin@test.com',
        'email' => 'admin@test.com',
        'password' => password_hash('Admin123!', PASSWORD_DEFAULT),
        'role' => 'admin',
        'full_name' => 'Test Administrator'
    ]
];

foreach ($users as $user) {
    // Insert into your users table
    // Adjust fields according to your schema
}
```

## Verification

After creating test users, verify they work:

```bash
# Test student login
curl -X POST http://localhost/stu/public/login.php \
  -d "username=student@test.com&password=Student123!"

# Or simply visit: http://localhost/stu/public/login.php
# And login with any of the test credentials
```

## Security Note

⚠️ **IMPORTANT**: These are TEST credentials only!
- Never use these credentials in production
- Delete test users from production databases
- Use different, secure passwords for production accounts
- Test credentials are intentionally simple for testing purposes

## Test Usage

Tests will use these credentials automatically:

```php
// Login tests
$I->fillField('username', 'student@test.com');
$I->fillField('password', 'Student123!');

// Feedback tests (as student)
$I->amLoggedInAs('student@test.com', 'Student123!');

// Admin tests
$I->amLoggedInAs('admin@test.com', 'Admin123!');
```

## Troubleshooting

### "Invalid credentials" error during tests
- Verify test users exist in database
- Check password hashing matches your system
- Confirm user roles are correct
- Check database connection in `tests/functional.suite.yml`

### "Email already exists" test fails
- Ensure `student@test.com` exists in database
- This test validates duplicate email prevention

### Tests can't access protected pages
- Verify session handling in your application
- Check that login redirects work correctly
- Ensure cookies are enabled in test configuration

## Database Configuration

Tests use these database settings (from `tests/functional.suite.yml`):

```yaml
Db:
    dsn: 'mysql:host=localhost;dbname=student_feedback'
    user: 'root'
    password: ''
```

Update if your database configuration is different.
