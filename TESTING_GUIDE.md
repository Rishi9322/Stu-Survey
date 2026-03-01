# Testing Framework Guide: Codeception + Behat

## Overview

This project uses two complementary PHP testing frameworks:

- **Codeception**: Technical testing framework for unit, functional, and acceptance tests
- **Behat**: Behavior-Driven Development (BDD) framework for stakeholder-readable scenarios

## Quick Start

### 1. Install Dependencies

```bash
composer install
```

### 2. Initialize Frameworks

```bash
# Build Codeception actor classes
vendor\bin\codecept build

# Verify Behat installation
vendor\bin\behat --version
```

### 3. Run Tests

```bash
# Run all tests (quick)
run_all_tests.bat

# Or run individually:
vendor\bin\codecept run              # All Codeception tests
vendor\bin\codecept run unit         # Unit tests only
vendor\bin\codecept run functional   # Functional tests only
vendor\bin\codecept run acceptance   # Acceptance tests only
vendor\bin\behat                     # All Behat scenarios
```

## Framework Breakdown

### Codeception Tests

#### Unit Tests (`tests/unit/`)
- **Purpose**: Test individual classes and methods in isolation
- **Location**: `tests/unit/`
- **Example**: `UserCest.php` - Tests User class methods
- **When to use**: Testing business logic, validation, data manipulation

#### Functional Tests (`tests/functional/`)
- **Purpose**: Test application functionality with simulated HTTP requests
- **Location**: `tests/functional/`
- **Examples**: 
  - `LoginCest.php` - Login/logout flows
  - `FeedbackCest.php` - Feedback submission
  - `ApiCest.php` - API endpoints
- **When to use**: Testing user flows, API endpoints, database interactions

#### Acceptance Tests (`tests/acceptance/`)
- **Purpose**: Test complete user scenarios from UI perspective
- **Location**: `tests/acceptance/`
- **Example**: `RegistrationCest.php` - Full registration workflow
- **When to use**: End-to-end testing, user journeys

### Behat Tests (BDD)

#### Feature Files (`features/`)
- **Purpose**: Human-readable test scenarios for stakeholders
- **Location**: `features/`
- **Examples**:
  - `login.feature` - Login scenarios
  - `registration.feature` - Registration scenarios
  - `feedback.feature` - Feedback management
  - `admin.feature` - Admin operations
- **When to use**: Requirements documentation, stakeholder communication, acceptance criteria

## Configuration Files

### Codeception Configuration

**Main Config**: `codeception.yml`
```yaml
paths:
    tests: tests
    output: tests/_output
```

**Suite Configs**:
- `tests/unit.suite.yml` - Unit test configuration
- `tests/functional.suite.yml` - Functional test configuration  
- `tests/acceptance.suite.yml` - Acceptance test configuration

### Behat Configuration

**Main Config**: `behat.yml`
```yaml
default:
    suites:
        default:
            contexts:
                - FeatureContext:
                    base_url: 'http://localhost/stu/public'
```

## Writing Tests

### Codeception Test Example

```php
<?php
namespace Tests\Functional;
use Tests\Support\FunctionalTester;

class LoginCest
{
    public function testSuccessfulLogin(FunctionalTester $I)
    {
        $I->amOnPage('/login.php');
        $I->fillField('username', 'test@example.com');
        $I->fillField('password', 'password123');
        $I->click('Login');
        $I->see('Dashboard');
    }
}
```

### Behat Feature Example

```gherkin
Feature: User Login
  Scenario: Successful login
    Given I am on "/login.php"
    When I fill in "username" with "test@example.com"
    And I fill in "password" with "password123"
    And I press "Login"
    Then I should see "Dashboard"
```

## Database Setup

For tests requiring database:

1. Create test database:
```sql
CREATE DATABASE student_feedback_test;
```

2. Update `tests/functional.suite.yml`:
```yaml
Db:
    dsn: 'mysql:host=localhost;dbname=student_feedback_test'
    user: 'root'
    password: ''
```

3. Export your schema:
```bash
php -r "system('mysqldump -u root student_feedback > tests/_data/dump.sql');"
```

## Running Specific Tests

```bash
# Run specific test file
vendor\bin\codecept run tests\functional\LoginCest.php

# Run specific test method
vendor\bin\codecept run tests\functional\LoginCest.php:testSuccessfulLogin

# Run with verbose output
vendor\bin\codecept run functional -v

# Run specific Behat feature
vendor\bin\behat features/login.feature

# Run specific scenario
vendor\bin\behat features/login.feature:5
```

## Test Reports

After running tests, reports are generated in `tests/_output/`:

- `report.html` - Codeception HTML report
- `behat_report.html` - Behat HTML report
- `failed` - List of failed tests for re-running

## Best Practices

### Codeception
1. Use `_before()` for test setup, `_after()` for cleanup
2. Keep unit tests fast and isolated
3. Use Page Objects for complex UI testing
4. Leverage database cleanup between tests

### Behat
1. Write scenarios in business language
2. Keep scenarios focused and independent
3. Reuse step definitions across features
4. Use Background for common setup steps

## Troubleshooting

### Common Issues

**Issue**: `Class 'UnitTester' not found`
**Solution**: Run `vendor\bin\codecept build`

**Issue**: Database connection fails
**Solution**: Verify credentials in `tests/functional.suite.yml`

**Issue**: Behat context not found
**Solution**: Run `vendor\bin\behat --init`

**Issue**: Tests are slow
**Solution**: Disable database cleanup or use transactions

## Additional Resources

- [Codeception Documentation](https://codeception.com/docs)
- [Behat Documentation](https://behat.org/en/latest/)
- [Mink Documentation](http://mink.behat.org/)

## Support

For issues or questions:
1. Check test output in `tests/_output/`
2. Run tests with `-v` flag for verbose output
3. Review configuration files
4. Check server and database status
