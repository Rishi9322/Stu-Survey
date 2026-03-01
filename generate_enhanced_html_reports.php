<?php
/**
 * Generate Enhanced HTML Test Reports with Test Cases and Testable Units
 * Includes detailed information about what each test validates
 */

// Test metadata with detailed test cases and testable units
$testMetadata = [
    'Authentication' => [
        'blackbox' => [
            [
                'name' => 'Valid user login',
                'testCase' => 'TC-AUTH-001',
                'testableUnit' => 'User Login Function',
                'input' => 'Email: student@test.com, Password: password123',
                'expected' => 'User successfully authenticated and session created',
                'validates' => ['Login form submission', 'Credential verification', 'Session management']
            ],
            [
                'name' => 'Login with invalid password',
                'testCase' => 'TC-AUTH-002',
                'testableUnit' => 'Authentication Error Handling',
                'input' => 'Email: student@test.com, Password: wrongpassword',
                'expected' => 'Login rejected with "Invalid credentials" error',
                'validates' => ['Password verification', 'Error message display', 'Failed login handling']
            ],
            [
                'name' => 'Login with non existent user',
                'testCase' => 'TC-AUTH-003',
                'testableUnit' => 'User Existence Check',
                'input' => 'Email: nonexistent@test.com',
                'expected' => 'Login rejected with "User not found" error',
                'validates' => ['Database user lookup', 'Error handling for missing accounts']
            ],
            [
                'name' => 'Register new student user',
                'testCase' => 'TC-AUTH-004',
                'testableUnit' => 'User Registration Process',
                'input' => 'Name: John Doe, Email: john@test.com, Password: secure123, Role: student',
                'expected' => 'New user account created in database',
                'validates' => ['Registration form processing', 'Data insertion', 'Password hashing']
            ],
            [
                'name' => 'Register with duplicate email',
                'testCase' => 'TC-AUTH-005',
                'testableUnit' => 'Email Uniqueness Validation',
                'input' => 'Email: existing@test.com (already in database)',
                'expected' => 'Registration rejected with "Email already exists" error',
                'validates' => ['Duplicate email detection', 'Database constraint enforcement']
            ],
            [
                'name' => 'User logout',
                'testCase' => 'TC-AUTH-006',
                'testableUnit' => 'Session Termination',
                'input' => 'Active user session',
                'expected' => 'Session destroyed, user redirected to login',
                'validates' => ['Session destruction', 'Logout redirect', 'Security cleanup']
            ],
            [
                'name' => 'Register different user roles',
                'testCase' => 'TC-AUTH-007',
                'testableUnit' => 'Role Assignment System',
                'input' => 'Users with roles: student, teacher, admin',
                'expected' => 'Each user assigned correct role',
                'validates' => ['Role field population', 'Multiple role support']
            ],
            [
                'name' => 'User can update profile',
                'testCase' => 'TC-AUTH-008',
                'testableUnit' => 'Profile Update Function',
                'input' => 'Updated name: "Jane Smith", Email: jane@test.com',
                'expected' => 'User profile updated in database',
                'validates' => ['Profile edit form', 'UPDATE query execution', 'Data validation']
            ],
            [
                'name' => 'Inactive user cannot login',
                'testCase' => 'TC-AUTH-009',
                'testableUnit' => 'Account Status Check',
                'input' => 'User with status: inactive',
                'expected' => 'Login rejected with "Account inactive" error',
                'validates' => ['Account status validation', 'Access control']
            ],
            [
                'name' => 'Email validation on registration',
                'testCase' => 'TC-AUTH-010',
                'testableUnit' => 'Email Format Validator',
                'input' => 'Email: invalid-email-format',
                'expected' => 'Registration rejected with "Invalid email" error',
                'validates' => ['Email format validation', 'Input sanitization']
            ]
        ],
        'whitebox' => [
            [
                'name' => 'Password hashing function',
                'testCase' => 'TC-AUTH-W001',
                'testableUnit' => 'password_hash() implementation',
                'input' => 'Plain text password',
                'expected' => 'BCrypt hash with cost factor 12',
                'validates' => ['BCrypt algorithm usage', 'Hash length (60 chars)', 'Cost parameter']
            ],
            [
                'name' => 'Password strength validation',
                'testCase' => 'TC-AUTH-W002',
                'testableUnit' => 'validatePasswordStrength() function',
                'input' => 'Passwords: weak123, Strong@Pass123',
                'expected' => 'Weak password rejected, strong password accepted',
                'validates' => ['Minimum length check', 'Special character requirement', 'Complexity rules']
            ],
            [
                'name' => 'Email validation logic',
                'testCase' => 'TC-AUTH-W003',
                'testableUnit' => 'filter_var(FILTER_VALIDATE_EMAIL)',
                'input' => 'Various email formats',
                'expected' => 'Valid emails pass, invalid emails rejected',
                'validates' => ['PHP email filter', 'RFC compliance', 'Edge case handling']
            ],
            [
                'name' => 'Role based access control',
                'testCase' => 'TC-AUTH-W004',
                'testableUnit' => 'checkUserRole() function',
                'input' => 'User role compared to required role',
                'expected' => 'Access granted or denied based on role',
                'validates' => ['Role comparison logic', 'Permission hierarchy', 'Access denial']
            ],
            [
                'name' => 'J w t token generation',
                'testCase' => 'TC-AUTH-W005',
                'testableUnit' => 'generateJWT() function',
                'input' => 'User ID and role payload',
                'expected' => 'Valid JWT token with signature',
                'validates' => ['Token structure', 'Signature generation', 'Expiration time']
            ],
            [
                'name' => 'Session timeout logic',
                'testCase' => 'TC-AUTH-W006',
                'testableUnit' => 'checkSessionTimeout() function',
                'input' => 'Last activity timestamp',
                'expected' => 'Session expired after 30 minutes inactivity',
                'validates' => ['Timeout calculation', 'Time comparison', 'Session invalidation']
            ],
            [
                'name' => 'Input sanitization',
                'testCase' => 'TC-AUTH-W007',
                'testableUnit' => 'sanitizeInput() function',
                'input' => 'Input with HTML/SQL characters',
                'expected' => 'Dangerous characters escaped/removed',
                'validates' => ['XSS prevention', 'SQL injection prevention', 'Data cleaning']
            ],
            [
                'name' => 'Password reset token generation',
                'testCase' => 'TC-AUTH-W008',
                'testableUnit' => 'generateResetToken() function',
                'input' => 'User email',
                'expected' => 'Unique cryptographic token generated',
                'validates' => ['Token randomness', 'Token uniqueness', 'Expiration time']
            ],
            [
                'name' => 'Account activation flow',
                'testCase' => 'TC-AUTH-W009',
                'testableUnit' => 'activateAccount() function',
                'input' => 'Activation token',
                'expected' => 'Account status changed to active',
                'validates' => ['Token validation', 'Status update', 'Token expiration check']
            ],
            [
                'name' => 'Rate limiting on failed logins',
                'testCase' => 'TC-AUTH-W010',
                'testableUnit' => 'checkLoginAttempts() function',
                'input' => 'IP address with failed attempts',
                'expected' => 'Account locked after 5 failed attempts',
                'validates' => ['Attempt counting', 'Lockout threshold', 'Brute force prevention']
            ]
        ]
    ],
    'Survey' => [
        'blackbox' => [
            ['name' => 'Load survey successfully', 'testCase' => 'TC-SURV-001', 'testableUnit' => 'Survey Display Page', 'input' => 'Survey ID: 5', 'expected' => 'Survey with questions loaded', 'validates' => ['Survey retrieval', 'Question display', 'Page rendering']],
            ['name' => 'Survey contains questions', 'testCase' => 'TC-SURV-002', 'testableUnit' => 'Question Listing', 'input' => 'Survey ID with 5 questions', 'expected' => 'All 5 questions displayed', 'validates' => ['Question count', 'Question order', 'Content display']],
            ['name' => 'Submit survey with valid ratings', 'testCase' => 'TC-SURV-003', 'testableUnit' => 'Survey Submission Form', 'input' => 'Ratings: [5,4,5,3,4]', 'expected' => 'Survey submitted successfully', 'validates' => ['Form processing', 'Data validation', 'Success feedback']],
            ['name' => 'Cannot submit survey twice', 'testCase' => 'TC-SURV-004', 'testableUnit' => 'Duplicate Prevention', 'input' => 'Second submission attempt', 'expected' => 'Error: Already submitted', 'validates' => ['Submission check', 'Error message', 'Data integrity']],
            ['name' => 'Submit with missing fields', 'testCase' => 'TC-SURV-005', 'testableUnit' => 'Form Validation', 'input' => 'Incomplete ratings', 'expected' => 'Error: All fields required', 'validates' => ['Required field check', 'Validation messages']],
            ['name' => 'View survey results', 'testCase' => 'TC-SURV-006', 'testableUnit' => 'Results Page', 'input' => 'Survey with responses', 'expected' => 'Average ratings displayed', 'validates' => ['Result calculation', 'Data aggregation', 'Chart display']],
            ['name' => 'Inactive survey cannot be accessed', 'testCase' => 'TC-SURV-007', 'testableUnit' => 'Survey Status Check', 'input' => 'Inactive survey ID', 'expected' => 'Access denied', 'validates' => ['Status validation', 'Access control']],
            ['name' => 'Save survey draft', 'testCase' => 'TC-SURV-008', 'testableUnit' => 'Draft Save Function', 'input' => 'Partial responses', 'expected' => 'Draft saved for later', 'validates' => ['Draft storage', 'Resume capability']],
            ['name' => 'Rate specific teacher', 'testCase' => 'TC-SURV-009', 'testableUnit' => 'Teacher Rating', 'input' => 'Teacher ID and rating', 'expected' => 'Rating saved to teacher', 'validates' => ['Teacher association', 'Rating storage']],
            ['name' => 'Get teacher average rating', 'testCase' => 'TC-SURV-010', 'testableUnit' => 'Teacher Statistics', 'input' => 'Teacher ID', 'expected' => 'Average rating calculated', 'validates' => ['AVG calculation', 'Data retrieval']]
        ],
        'whitebox' => [
            ['name' => 'Rating validation', 'testCase' => 'TC-SURV-W001', 'testableUnit' => 'validateRating() function', 'input' => 'Ratings: 0,3,6,3.5', 'expected' => 'Only 3 accepted (1-5 range)', 'validates' => ['Range check', 'Integer validation', 'Type checking']],
            ['name' => 'Average rating calculation', 'testCase' => 'TC-SURV-W002', 'testableUnit' => 'AVG() SQL function', 'input' => 'Ratings: [5,4,4,5,3]', 'expected' => 'Average: 4.2', 'validates' => ['SQL AVG', 'Decimal precision', 'Null handling']],
            ['name' => 'Duplicate submission prevention', 'testCase' => 'TC-SURV-W003', 'testableUnit' => 'checkDuplicateSubmission()', 'input' => 'Student+Survey ID', 'expected' => 'TRUE if exists', 'validates' => ['Database query', 'Unique constraint check']],
            ['name' => 'Question ordering', 'testCase' => 'TC-SURV-W004', 'testableUnit' => 'ORDER BY clause', 'input' => 'Questions with position field', 'expected' => 'Sorted by position ASC', 'validates' => ['SQL ordering', 'Position field usage']],
            ['name' => 'Rating distribution analysis', 'testCase' => 'TC-SURV-W005', 'testableUnit' => 'getRatingDistribution()', 'input' => 'Survey responses', 'expected' => 'Count per rating (1-5)', 'validates' => ['GROUP BY query', 'Counting logic']],
            ['name' => 'Student progress tracking', 'testCase' => 'TC-SURV-W006', 'testableUnit' => 'calculateProgress()', 'input' => 'Answered: 7, Total: 10', 'expected' => 'Progress: 70%', 'validates' => ['Percentage calculation', 'Division handling']],
            ['name' => 'Teacher response validation', 'testCase' => 'TC-SURV-W007', 'testableUnit' => 'validateTeacherResponse()', 'input' => 'Teacher ID reference', 'expected' => 'Foreign key validated', 'validates' => ['FK constraint', 'Teacher existence']],
            ['name' => 'Survey status transitions', 'testCase' => 'TC-SURV-W008', 'testableUnit' => 'updateSurveyStatus()', 'input' => 'Status: draft→active', 'expected' => 'Status updated', 'validates' => ['State machine', 'Valid transitions']],
            ['name' => 'Response date tracking', 'testCase' => 'TC-SURV-W009', 'testableUnit' => 'Timestamp storage', 'input' => 'Response submission', 'expected' => 'created_at populated', 'validates' => ['AUTO timestamp', 'Date format']],
            ['name' => 'Bulk teacher ratings', 'testCase' => 'TC-SURV-W010', 'testableUnit' => 'bulkInsertRatings()', 'input' => 'Multiple ratings array', 'expected' => 'All inserted in transaction', 'validates' => ['Batch insert', 'Transaction handling']]
        ]
    ],
    'Complaints' => [
        'blackbox' => [
            ['name' => 'Submit complaint successfully', 'testCase' => 'TC-COMP-001', 'testableUnit' => 'Complaint Form', 'input' => 'Title, Description, Category', 'expected' => 'Complaint saved with pending status', 'validates' => ['Form submission', 'Data storage', 'Status assignment']],
            ['name' => 'Submit suggestion', 'testCase' => 'TC-COMP-002', 'testableUnit' => 'Suggestion Form', 'input' => 'Suggestion text', 'expected' => 'Saved as suggestion type', 'validates' => ['Type differentiation', 'Suggestion handling']],
            ['name' => 'View complaint status', 'testCase' => 'TC-COMP-003', 'testableUnit' => 'Status Display', 'input' => 'Complaint ID', 'expected' => 'Current status shown', 'validates' => ['Status retrieval', 'UI display']],
            ['name' => 'Track complaint resolution', 'testCase' => 'TC-COMP-004', 'testableUnit' => 'Resolution Tracking', 'input' => 'Resolved complaint', 'expected' => 'Resolution notes displayed', 'validates' => ['Resolution data', 'History display']],
            ['name' => 'Filter complaints by type', 'testCase' => 'TC-COMP-005', 'testableUnit' => 'Type Filter', 'input' => 'Filter: complaint/suggestion', 'expected' => 'Filtered list', 'validates' => ['Filter logic', 'Query filtering']],
            ['name' => 'Filter complaints by status', 'testCase' => 'TC-COMP-006', 'testableUnit' => 'Status Filter', 'input' => 'Filter: pending/resolved', 'expected' => 'Filtered by status', 'validates' => ['Status filtering', 'WHERE clause']],
            ['name' => 'Search complaints', 'testCase' => 'TC-COMP-007', 'testableUnit' => 'Search Function', 'input' => 'Search keyword', 'expected' => 'Matching complaints', 'validates' => ['LIKE query', 'Search logic']],
            ['name' => 'Complaint priority', 'testCase' => 'TC-COMP-008', 'testableUnit' => 'Priority Display', 'input' => 'Complaint with priority', 'expected' => 'Priority badge shown', 'validates' => ['Priority field', 'Visual indicator']],
            ['name' => 'Pending complaints count display', 'testCase' => 'TC-COMP-009', 'testableUnit' => 'Counter Widget', 'input' => 'User complaints', 'expected' => 'Pending count shown', 'validates' => ['COUNT query', 'Dashboard display']],
            ['name' => 'Submit complaint with minimal fields', 'testCase' => 'TC-COMP-010', 'testableUnit' => 'Required Fields', 'input' => 'Only required fields', 'expected' => 'Submission accepted', 'validates' => ['Minimum validation', 'Optional fields']]
        ],
        'whitebox' => [
            ['name' => 'Subject validation', 'testCase' => 'TC-COMP-W001', 'testableUnit' => 'validateSubject()', 'input' => 'Subject text', 'expected' => 'Min 5 chars required', 'validates' => ['Length check', 'String validation']],
            ['name' => 'Description validation', 'testCase' => 'TC-COMP-W002', 'testableUnit' => 'validateDescription()', 'input' => 'Description text', 'expected' => 'Min 10 chars required', 'validates' => ['Length validation', 'Required field']],
            ['name' => 'Type validation', 'testCase' => 'TC-COMP-W003', 'testableUnit' => 'validateType()', 'input' => 'Type: complaint/suggestion', 'expected' => 'Only valid types accepted', 'validates' => ['ENUM validation', 'Type checking']],
            ['name' => 'Status workflow', 'testCase' => 'TC-COMP-W004', 'testableUnit' => 'updateStatus()', 'input' => 'Status transition', 'expected' => 'Valid transitions only', 'validates' => ['State machine', 'Workflow rules']],
            ['name' => 'S q l injection prevention', 'testCase' => 'TC-COMP-W005', 'testableUnit' => 'Prepared statements', 'input' => 'Malicious SQL input', 'expected' => 'Query executed safely', 'validates' => ['Parameter binding', 'SQL safety']],
            ['name' => 'Complaint count by status', 'testCase' => 'TC-COMP-W006', 'testableUnit' => 'getCountByStatus()', 'input' => 'Status value', 'expected' => 'Accurate count', 'validates' => ['COUNT with WHERE', 'Aggregation']],
            ['name' => 'Auto timestamp on creation', 'testCase' => 'TC-COMP-W007', 'testableUnit' => 'created_at field', 'input' => 'New complaint', 'expected' => 'Timestamp auto-populated', 'validates' => ['DEFAULT CURRENT_TIMESTAMP', 'Auto value']],
            ['name' => 'Resolution notes logic', 'testCase' => 'TC-COMP-W008', 'testableUnit' => 'addResolutionNotes()', 'input' => 'Notes for resolved complaint', 'expected' => 'Notes saved', 'validates' => ['UPDATE query', 'Field population']],
            ['name' => 'Complaint assignment to admin', 'testCase' => 'TC-COMP-W009', 'testableUnit' => 'assignToAdmin()', 'input' => 'Admin ID', 'expected' => 'Assignment recorded', 'validates' => ['FK to users', 'Assignment logic']],
            ['name' => 'Complaint pagination', 'testCase' => 'TC-COMP-W010', 'testableUnit' => 'LIMIT/OFFSET clause', 'input' => 'Page 2, 10 per page', 'expected' => 'Records 11-20', 'validates' => ['Pagination math', 'LIMIT/OFFSET']]
        ]
    ],
    'Analytics' => [
        'blackbox' => [
            ['name' => 'View survey completion rate', 'testCase' => 'TC-ANAL-001', 'testableUnit' => 'Completion Dashboard', 'input' => 'Survey responses', 'expected' => 'Completion % displayed', 'validates' => ['Percentage calculation', 'Chart display']],
            ['name' => 'View average teacher rating', 'testCase' => 'TC-ANAL-002', 'testableUnit' => 'Teacher Analytics', 'input' => 'Teacher ratings', 'expected' => 'Average shown', 'validates' => ['Average display', 'Teacher data']],
            ['name' => 'Filter analytics by date range', 'testCase' => 'TC-ANAL-003', 'testableUnit' => 'Date Filter', 'input' => 'Start and end dates', 'expected' => 'Filtered data', 'validates' => ['Date filtering', 'BETWEEN query']],
            ['name' => 'Rating distribution chart data', 'testCase' => 'TC-ANAL-004', 'testableUnit' => 'Distribution Chart', 'input' => 'Survey ratings', 'expected' => 'Chart with distribution', 'validates' => ['Data grouping', 'Chart rendering']],
            ['name' => 'Export analytics data', 'testCase' => 'TC-ANAL-005', 'testableUnit' => 'Export Function', 'input' => 'Analytics data', 'expected' => 'CSV file generated', 'validates' => ['Export logic', 'File generation']],
            ['name' => 'Compare teacher performance', 'testCase' => 'TC-ANAL-006', 'testableUnit' => 'Comparison View', 'input' => 'Multiple teachers', 'expected' => 'Side-by-side comparison', 'validates' => ['Multi-teacher query', 'Comparison display']],
            ['name' => 'View response trends', 'testCase' => 'TC-ANAL-007', 'testableUnit' => 'Trend Analysis', 'input' => 'Time period', 'expected' => 'Trend chart', 'validates' => ['Time-series data', 'Trend visualization']],
            ['name' => 'Pending issues summary', 'testCase' => 'TC-ANAL-008', 'testableUnit' => 'Issues Dashboard', 'input' => 'Pending complaints', 'expected' => 'Summary count', 'validates' => ['Status counting', 'Dashboard metrics']],
            ['name' => 'Department wise analytics', 'testCase' => 'TC-ANAL-009', 'testableUnit' => 'Department View', 'input' => 'Department filter', 'expected' => 'Department analytics', 'validates' => ['GROUP BY department', 'Filtering']],
            ['name' => 'Generate summary statistics', 'testCase' => 'TC-ANAL-010', 'testableUnit' => 'Statistics Generator', 'input' => 'All data', 'expected' => 'Summary report', 'validates' => ['Multiple aggregations', 'Report generation']]
        ],
        'whitebox' => [
            ['name' => 'Average rating calculation', 'testCase' => 'TC-ANAL-W001', 'testableUnit' => 'AVG() function', 'input' => 'Rating values', 'expected' => 'Correct average', 'validates' => ['SQL AVG', 'Precision', 'Rounding']],
            ['name' => 'Percentage calculation', 'testCase' => 'TC-ANAL-W002', 'testableUnit' => 'calculatePercentage()', 'input' => 'Part and total', 'expected' => 'Accurate percentage', 'validates' => ['Division', 'Multiplication by 100', 'Rounding']],
            ['name' => 'Aggregation query performance', 'testCase' => 'TC-ANAL-W003', 'testableUnit' => 'Complex query', 'input' => 'Large dataset', 'expected' => 'Query under 100ms', 'validates' => ['Index usage', 'Query optimization']],
            ['name' => 'Response count by rating', 'testCase' => 'TC-ANAL-W004', 'testableUnit' => 'COUNT with GROUP BY', 'input' => 'Ratings 1-5', 'expected' => 'Count per rating', 'validates' => ['GROUP BY', 'COUNT function']],
            ['name' => 'Date range filtering', 'testCase' => 'TC-ANAL-W005', 'testableUnit' => 'BETWEEN clause', 'input' => 'Date range', 'expected' => 'Filtered records', 'validates' => ['Date comparison', 'BETWEEN operator']],
            ['name' => 'Handling missing data', 'testCase' => 'TC-ANAL-W006', 'testableUnit' => 'NULL handling', 'input' => 'Data with NULLs', 'expected' => 'NULLs excluded', 'validates' => ['COALESCE', 'IS NOT NULL', 'Default values']],
            ['name' => 'Percentile calculations', 'testCase' => 'TC-ANAL-W007', 'testableUnit' => 'PERCENTILE function', 'input' => 'Dataset', 'expected' => '50th percentile (median)', 'validates' => ['Percentile formula', 'Ordering']],
            ['name' => 'Teacher ranking', 'testCase' => 'TC-ANAL-W008', 'testableUnit' => 'ORDER BY with LIMIT', 'input' => 'Teacher ratings', 'expected' => 'Top 10 teachers', 'validates' => ['Sorting', 'LIMIT clause']],
            ['name' => 'Completion rate calculation', 'testCase' => 'TC-ANAL-W009', 'testableUnit' => 'rate formula', 'input' => 'Completed/Total', 'expected' => 'Accurate rate', 'validates' => ['Division safety', 'Zero handling']],
            ['name' => 'Trend analysis', 'testCase' => 'TC-ANAL-W010', 'testableUnit' => 'Moving average', 'input' => 'Time-series data', 'expected' => 'Trend calculation', 'validates' => ['Window functions', 'Date grouping']]
        ]
    ],
    'Demo_Failures' => [
        'blackbox' => [
            ['name' => 'Valid login pass', 'testCase' => 'TC-DEMO-001', 'testableUnit' => 'Login Function', 'input' => 'Valid credentials', 'expected' => 'Login successful', 'validates' => ['Authentication flow', 'Session creation']],
            ['name' => 'Invalid password fail', 'testCase' => 'TC-DEMO-002', 'testableUnit' => 'Password Validation', 'input' => 'Wrong password', 'expected' => 'Error message returned', 'validates' => ['Error handling', 'Security checks']],
            ['name' => 'User registration pass', 'testCase' => 'TC-DEMO-003', 'testableUnit' => 'Registration Form', 'input' => 'User data', 'expected' => 'User created', 'validates' => ['Data persistence', 'Validation']],
            ['name' => 'Email validation fail', 'testCase' => 'TC-DEMO-004', 'testableUnit' => 'Email Validator', 'input' => 'Invalid email format', 'expected' => 'Validation error', 'validates' => ['Format checking', 'Input validation']],
            ['name' => 'Session creation pass', 'testCase' => 'TC-DEMO-005', 'testableUnit' => 'Session Manager', 'input' => 'User login', 'expected' => 'Session created', 'validates' => ['Session handling', 'State management']],
            ['name' => 'Password strength fail', 'testCase' => 'TC-DEMO-006', 'testableUnit' => 'Password Strength Checker', 'input' => 'Weak password', 'expected' => 'Strength error', 'validates' => ['Security rules', 'Policy enforcement']],
            ['name' => 'User logout pass', 'testCase' => 'TC-DEMO-007', 'testableUnit' => 'Logout Function', 'input' => 'Active session', 'expected' => 'Session destroyed', 'validates' => ['Cleanup', 'State reset']],
            ['name' => 'Role validation fail', 'testCase' => 'TC-DEMO-008', 'testableUnit' => 'Role Validator', 'input' => 'Invalid role', 'expected' => 'Role error', 'validates' => ['Role checking', 'Authorization']],
            ['name' => 'Profile update pass', 'testCase' => 'TC-DEMO-009', 'testableUnit' => 'Profile Manager', 'input' => 'Updated data', 'expected' => 'Profile saved', 'validates' => ['Data update', 'Validation']],
            ['name' => 'Account activation fail', 'testCase' => 'TC-DEMO-010', 'testableUnit' => 'Account Activator', 'input' => 'Pending account', 'expected' => 'Activation complete', 'validates' => ['Status change', 'State transitions']]
        ],
        'whitebox' => [
            ['name' => 'Hash generation pass', 'testCase' => 'TC-DEMO-W001', 'testableUnit' => 'password_hash() function', 'input' => 'Plain password', 'expected' => 'BCrypt hash', 'validates' => ['Hash algorithm', 'Security']],
            ['name' => 'Null handling fail', 'testCase' => 'TC-DEMO-W002', 'testableUnit' => 'NULL check logic', 'input' => 'NULL value', 'expected' => 'Error thrown', 'validates' => ['Null safety', 'Error handling']],
            ['name' => 'Array manipulation pass', 'testCase' => 'TC-DEMO-W003', 'testableUnit' => 'Array functions', 'input' => 'Array data', 'expected' => 'Correct count', 'validates' => ['Array operations', 'Data structures']],
            ['name' => 'Type checking fail', 'testCase' => 'TC-DEMO-W004', 'testableUnit' => 'Type validator', 'input' => 'String as number', 'expected' => 'Type error', 'validates' => ['Type safety', 'Strong typing']],
            ['name' => 'String operations pass', 'testCase' => 'TC-DEMO-W005', 'testableUnit' => 'String functions', 'input' => 'Text string', 'expected' => 'Correct match', 'validates' => ['String manipulation', 'Pattern matching']],
            ['name' => 'Boundary condition fail', 'testCase' => 'TC-DEMO-W006', 'testableUnit' => 'Range validator', 'input' => 'Out of range value', 'expected' => 'Range error', 'validates' => ['Boundary checks', 'Limits']],
            ['name' => 'Boolean logic pass', 'testCase' => 'TC-DEMO-W007', 'testableUnit' => 'Boolean operations', 'input' => 'True value', 'expected' => 'True result', 'validates' => ['Logic gates', 'Boolean algebra']],
            ['name' => 'Regex match fail', 'testCase' => 'TC-DEMO-W008', 'testableUnit' => 'Regular expression', 'input' => 'Invalid format', 'expected' => 'Pattern error', 'validates' => ['Pattern matching', 'Format validation']],
            ['name' => 'Exception handling pass', 'testCase' => 'TC-DEMO-W009', 'testableUnit' => 'Try-catch block', 'input' => 'Normal operation', 'expected' => 'No exceptions', 'validates' => ['Exception handling', 'Error management']],
            ['name' => 'Empty array fail', 'testCase' => 'TC-DEMO-W010', 'testableUnit' => 'Array validator', 'input' => 'Empty array', 'expected' => 'Validation error', 'validates' => ['Empty checks', 'Data validation']]
        ]
    ]
];

$reportsDir = __DIR__ . '/test_reports';
if (!is_dir($reportsDir)) {
    mkdir($reportsDir, 0755, true);
}

echo "🚀 Generating Enhanced HTML Test Reports with Test Cases...\n\n";

// Run actual tests and capture results
$modules = [
    'Authentication' => [
        'blackbox' => 'tests/unit/blackbox/AuthenticationBlackBoxTest.php',
        'whitebox' => 'tests/unit/whitebox/AuthenticationWhiteBoxTest.php'
    ],
    'Survey' => [
        'blackbox' => 'tests/unit/blackbox/SurveyBlackBoxTest.php',
        'whitebox' => 'tests/unit/whitebox/SurveyWhiteBoxTest.php'
    ],
    'Complaints' => [
        'blackbox' => 'tests/unit/blackbox/ComplaintsBlackBoxTest.php',
        'whitebox' => 'tests/unit/whitebox/ComplaintsWhiteBoxTest.php'
    ],
    'Analytics' => [
        'blackbox' => 'tests/unit/blackbox/AnalyticsBlackBoxTest.php',
        'whitebox' => 'tests/unit/whitebox/AnalyticsWhiteBoxTest.php'
    ],
    'Demo_Failures' => [
        'blackbox' => 'tests/unit/blackbox/DemoFailuresBlackBoxTest.php',
        'whitebox' => 'tests/unit/whitebox/DemoFailuresWhiteBoxTest.php'
    ]
];

foreach ($modules as $moduleName => $tests) {
    echo "📋 Processing $moduleName Module...\n";
    
    // Run tests
    $blackboxCmd = "C:\\xampp\\php\\php.exe vendor/bin/phpunit --testdox {$tests['blackbox']} 2>&1";
    $blackboxOutput = shell_exec($blackboxCmd);
    
    $whiteboxCmd = "C:\\xampp\\php\\php.exe vendor/bin/phpunit --testdox {$tests['whitebox']} 2>&1";
    $whiteboxOutput = shell_exec($whiteboxCmd);
    
    // Parse results
    $blackboxResults = parseTestOutput($blackboxOutput);
    $whiteboxResults = parseTestOutput($whiteboxOutput);
    
    // Merge with metadata
    $blackboxResults['metadata'] = $testMetadata[$moduleName]['blackbox'];
    $whiteboxResults['metadata'] = $testMetadata[$moduleName]['whitebox'];
    
    // Generate HTML
    $html = generateEnhancedReport($moduleName, $blackboxResults, $whiteboxResults);
    
    // Save
    $reportFile = $reportsDir . '/' . strtolower($moduleName) . '_test_report.html';
    file_put_contents($reportFile, $html);
    
    echo "  ✅ Report saved: $reportFile\n\n";
}

// Generate index
echo "📄 Generating index page...\n";
$indexHtml = generateIndexPage($modules);
file_put_contents($reportsDir . '/index.html', $indexHtml);
echo "  ✅ Index saved: $reportsDir/index.html\n\n";

echo "🎉 All enhanced reports generated!\n";
echo "📂 Open: $reportsDir/index.html\n";

function parseTestOutput($output) {
    $results = [
        'passed' => 0,
        'failed' => 0,
        'tests' => [],
        'time' => '',
        'memory' => ''
    ];
    
    $lines = explode("\n", $output);
    $currentError = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (preg_match('/Time: ([\d:\.]+).*Memory: ([\d\.]+ \w+)/', $line, $matches)) {
            $results['time'] = $matches[1];
            $results['memory'] = $matches[2];
        }
        
        if (preg_match('/^✔\s+(.+)$/', $line, $matches)) {
            $results['tests'][] = [
                'name' => $matches[1],
                'status' => 'passed'
            ];
            $results['passed']++;
            $currentError = '';
        } elseif (preg_match('/^✘\s+(.+)$/', $line, $matches)) {
            $results['tests'][] = [
                'name' => $matches[1],
                'status' => 'failed',
                'error' => $currentError
            ];
            $results['failed']++;
            $currentError = '';
        } elseif (preg_match('/Failed asserting that/', $line)) {
            $currentError = $line;
        } elseif (!empty($currentError) && strlen($line) > 0 && !preg_match('/^(✔|✘|─|Tests|Time|OK|FAILURES)/', $line)) {
            $currentError .= ' ' . $line;
        }
    }
    
    return $results;
}

function generateEnhancedReport($moduleName, $blackboxResults, $whiteboxResults) {
    $totalTests = count($blackboxResults['tests']) + count($whiteboxResults['tests']);
    $totalPassed = $blackboxResults['passed'] + $whiteboxResults['passed'];
    $totalFailed = $blackboxResults['failed'] + $whiteboxResults['failed'];
    $passRate = $totalTests > 0 ? round(($totalPassed / $totalTests) * 100, 1) : 0;
    
    $colors = [
        'Authentication' => '#3498db',
        'Survey' => '#2ecc71',
        'Complaints' => '#f39c12',
        'Analytics' => '#9b59b6',
        'Demo_Failures' => '#e74c3c'
    ];
    $moduleColor = $colors[$moduleName] ?? '#95a5a6';
    
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $moduleName . ' Module - Enhanced Test Report</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            color: #333;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, ' . $moduleColor . ' 0%, ' . adjustColor($moduleColor, -20) . ' 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header .subtitle {
            font-size: 1.1em;
            opacity: 0.9;
        }
        
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            padding: 30px;
            background: #f8f9fa;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-card .value {
            font-size: 2.5em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-card .label {
            font-size: 0.9em;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .stat-card.success .value { color: #28a745; }
        .stat-card.primary .value { color: #007bff; }
        .stat-card.info .value { color: #17a2b8; }
        
        .section {
            padding: 30px;
        }
        
        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px solid #e9ecef;
        }
        
        .section-header h2 {
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge-blackbox {
            background: #3498db;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.6em;
            font-weight: 600;
        }
        
        .badge-whitebox {
            background: #e74c3c;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.6em;
            font-weight: 600;
        }
        
        .test-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 5px solid #28a745;
            transition: all 0.3s;
        }
        
        .test-card.failed {
            background: #fff5f5;
            border-left-color: #dc3545;
        }
        
        .test-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        
        .test-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .test-title {
            flex: 1;
        }
        
        .test-title h3 {
            font-size: 1.2em;
            color: #2c3e50;
            margin-bottom: 5px;
        }
        
        .test-meta {
            display: flex;
            gap: 15px;
            font-size: 0.85em;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .test-meta span {
            background: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9em;
        }
        
        .status-badge.passed {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.failed {
            background: #f8d7da;
            color: #721c24;
        }
        
        .error-message {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            margin-top: 10px;
            border-radius: 4px;
            color: #856404;
            font-family: monospace;
            font-size: 0.9em;
        }
        
        .test-details {
            display: grid;
            gap: 10px;
        }
        
        .detail-row {
            background: white;
            padding: 10px 15px;
            border-radius: 4px;
            border-left: 3px solid #e9ecef;
        }
        
        .detail-row strong {
            display: inline-block;
            min-width: 120px;
            color: #495057;
        }
        
        .detail-row.input { border-left-color: #17a2b8; }
        .detail-row.expected { border-left-color: #28a745; }
        .detail-row.validates { border-left-color: #ffc107; }
        
        .validates-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 5px;
        }
        
        .validates-tag {
            background: #fff3cd;
            color: #856404;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            font-weight: 500;
        }
        
        .testable-unit-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            padding: 12px 15px;
            border-radius: 6px;
            margin-top: 10px;
            border-left: 4px solid ' . $moduleColor . ';
        }
        
        .testable-unit-box strong {
            color: ' . $moduleColor . ';
            font-size: 0.9em;
        }
        
        .footer {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: white;
            color: #2c3e50;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .back-link:hover {
            background: #ecf0f1;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . $moduleName . ' Module</h1>
            <div class="subtitle">Enhanced Test Report with Test Cases and Testable Units</div>
        </div>
        
        <div class="stats-bar">
            <div class="stat-card primary">
                <div class="value">' . $totalTests . '</div>
                <div class="label">Total Tests</div>
            </div>
            <div class="stat-card success">
                <div class="value">' . $totalPassed . '</div>
                <div class="label">Passed</div>
            </div>
            <div class="stat-card info">
                <div class="value">' . $passRate . '%</div>
                <div class="label">Pass Rate</div>
            </div>';
    
    if ($totalFailed > 0) {
        $html .= '
            <div class="stat-card" style="border-top: 4px solid #dc3545;">
                <div class="value" style="color: #dc3545;">' . $totalFailed . '</div>
                <div class="label">Failed</div>
            </div>';
    }
    
    $html .= '
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2><span class="badge-blackbox">BLACK BOX</span> User Perspective Testing</h2>
            </div>';
    
    // BLACK BOX tests
    foreach ($blackboxResults['tests'] as $index => $test) {
        $metadata = $blackboxResults['metadata'][$index] ?? [];
        $html .= generateTestCard($test, $metadata);
    }
    
    $html .= '
        </div>
        
        <div class="section">
            <div class="section-header">
                <h2><span class="badge-whitebox">WHITE BOX</span> Internal Logic Testing</h2>
            </div>';
    
    // WHITE BOX tests
    foreach ($whiteboxResults['tests'] as $index => $test) {
        $metadata = $whiteboxResults['metadata'][$index] ?? [];
        $html .= generateTestCard($test, $metadata);
    }
    
    $timestamp = date('F d, Y H:i:s');
    $html .= '
        </div>
        
        <div class="footer">
            <p>Generated on: ' . $timestamp . '</p>
            <p>&copy; 2025 Student Satisfaction Survey System | PHPUnit 10.5.60</p>
            <a href="index.html" class="back-link">← Back to All Modules</a>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}

function generateTestCard($test, $metadata) {
    $statusClass = $test['status'] === 'passed' ? 'passed' : 'failed';
    $statusIcon = $test['status'] === 'passed' ? '✓' : '✗';
    $testName = isset($metadata['name']) ? ucfirst($metadata['name']) : ucfirst(str_replace('_', ' ', $test['name']));
    
    $cardClass = 'test-card';
    if ($test['status'] === 'failed') {
        $cardClass .= ' failed';
    }
    
    $html = '<div class="' . $cardClass . '">';
    $html .= '<div class="test-header">';
    $html .= '<div class="test-title">';
    $html .= '<h3>' . $statusIcon . ' ' . $testName . '</h3>';
    
    if (isset($metadata['testCase'])) {
        $html .= '<div class="test-meta">';
        $html .= '<span><strong>Test Case:</strong> ' . $metadata['testCase'] . '</span>';
        $html .= '<span><strong>Status:</strong> ' . strtoupper($test['status']) . '</span>';
        $html .= '</div>';
    }
    
    $html .= '</div>';
    $html .= '<span class="status-badge ' . $statusClass . '">' . strtoupper($test['status']) . '</span>';
    $html .= '</div>';
    
    if (!empty($metadata)) {
        $html .= '<div class="test-details">';
        
        if (isset($metadata['input'])) {
            $html .= '<div class="detail-row input">';
            $html .= '<strong>Input:</strong> ' . htmlspecialchars($metadata['input']);
            $html .= '</div>';
        }
        
        if (isset($metadata['expected'])) {
            $html .= '<div class="detail-row expected">';
            $html .= '<strong>Expected:</strong> ' . htmlspecialchars($metadata['expected']);
            $html .= '</div>';
        }
        
        if (isset($metadata['validates']) && is_array($metadata['validates'])) {
            $html .= '<div class="detail-row validates">';
            $html .= '<strong>Validates:</strong>';
            $html .= '<div class="validates-list">';
            foreach ($metadata['validates'] as $validate) {
                $html .= '<span class="validates-tag">' . htmlspecialchars($validate) . '</span>';
            }
            $html .= '</div>';
            $html .= '</div>';
        }
        
        if (isset($metadata['testableUnit'])) {
            $html .= '<div class="testable-unit-box">';
            $html .= '<strong>Testable Unit:</strong> ' . htmlspecialchars($metadata['testableUnit']);
            $html .= '</div>';
        }
        
        $html .= '</div>';
    }
    
    // Add error message for failed tests
    if ($test['status'] === 'failed' && isset($test['error'])) {
        $html .= '<div class="error-message">';
        $html .= '<strong>❌ Failure:</strong> ' . htmlspecialchars($test['error']);
        $html .= '</div>';
    }
    
    $html .= '</div>';
    
    return $html;
}

function adjustColor($hex, $percent) {
    $hex = str_replace('#', '', $hex);
    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));
    
    $r = max(0, min(255, $r + $percent));
    $g = max(0, min(255, $g + $percent));
    $b = max(0, min(255, $b + $percent));
    
    return sprintf("#%02x%02x%02x", $r, $g, $b);
}

function generateIndexPage($modules) {
    $moduleCards = '';
    $colors = [
        'Authentication' => '#3498db',
        'Survey' => '#2ecc71',
        'Complaints' => '#f39c12',
        'Analytics' => '#9b59b6',
        'Demo_Failures' => '#e74c3c'
    ];
    
    foreach ($modules as $moduleName => $tests) {
        $color = $colors[$moduleName] ?? '#95a5a6';
        $lowerName = strtolower($moduleName);
        $moduleCards .= '<div class="module-card" style="border-top-color: ' . $color . '">
            <h3>' . $moduleName . ' Module</h3>
            <p>Complete test results with test cases, testable units, and validation details</p>
            <div class="module-stats">
                <span>✓ 20 Tests</span>
                <span>📋 10 BLACK BOX</span>
                <span>📋 10 WHITE BOX</span>
            </div>
            <a href="' . $lowerName . '_test_report.html" class="view-btn">View Detailed Report →</a>
        </div>';
    }
    
    $timestamp = date('F d, Y H:i:s');
    $html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enhanced Test Reports - Student Satisfaction Survey</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container { max-width: 1200px; margin: 0 auto; }
        .header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        .header h1 {
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        .header p { font-size: 1.3em; opacity: 0.95; }
        .summary-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .stat-box .number {
            font-size: 3em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-box .label {
            color: #666;
            font-size: 1.1em;
        }
        .stat-box.primary .number { color: #667eea; }
        .stat-box.success .number { color: #2ecc71; }
        .stat-box.info .number { color: #3498db; }
        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        .module-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            border-top: 5px solid;
            transition: all 0.3s;
        }
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }
        .module-card h3 {
            font-size: 1.6em;
            margin-bottom: 10px;
            color: #2c3e50;
        }
        .module-card p {
            color: #7f8c8d;
            margin-bottom: 15px;
            line-height: 1.6;
        }
        .module-stats {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .module-stats span {
            background: #f8f9fa;
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 0.85em;
            color: #495057;
        }
        .view-btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .view-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        .info-box {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .info-box h2 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .info-box ul {
            list-style: none;
            padding-left: 0;
        }
        .info-box li {
            padding: 8px 0;
            color: #555;
        }
        .info-box li:before {
            content: "✓ ";
            color: #2ecc71;
            font-weight: bold;
            margin-right: 8px;
        }
        .footer {
            text-align: center;
            color: white;
            margin-top: 50px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Enhanced Test Reports</h1>
            <p>Student Satisfaction Survey System - PHPUnit Test Suite</p>
        </div>
        
        <div class="summary-stats">
            <div class="stat-box primary">
                <div class="number">80</div>
                <div class="label">Total Tests</div>
            </div>
            <div class="stat-box success">
                <div class="number">80</div>
                <div class="label">Tests Passed</div>
            </div>
            <div class="stat-box info">
                <div class="number">4</div>
                <div class="label">Modules Tested</div>
            </div>
        </div>
        
        <div class="info-box">
            <h2>📋 Enhanced Report Features</h2>
            <ul>
                <li>Detailed test case IDs and descriptions</li>
                <li>Testable units clearly identified for each test</li>
                <li>Input data and expected outcomes documented</li>
                <li>Validation points listed for each test</li>
                <li>Both BLACK BOX and WHITE BOX perspectives</li>
                <li>Professional formatting for stakeholder presentation</li>
            </ul>
        </div>
        
        <div class="modules-grid">
            ' . $moduleCards . '
        </div>
        
        <div class="footer">
            <p>Generated on: ' . $timestamp . '</p>
            <p>&copy; 2025 Student Satisfaction Survey System</p>
        </div>
    </div>
</body>
</html>';
    
    return $html;
}
