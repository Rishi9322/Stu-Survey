<?php

namespace Tests\Unit\WhiteBox;

use PHPUnit\Framework\TestCase;

/**
 * Demo White Box Tests with Intentional Failures
 * These tests demonstrate internal logic failures in the HTML reports
 */
class DemoFailuresWhiteBoxTest extends TestCase
{
    /**
     * Test 1: This test will pass - Hash generation
     */
    public function testHashGenerationPass()
    {
        $hash = password_hash('password', PASSWORD_BCRYPT);
        $this->assertNotEmpty($hash, "Hash should not be empty");
    }

    /**
     * Test 2: This test will fail - Null handling
     */
    public function testNullHandlingFail()
    {
        $value = null;
        $this->assertNotNull($value, "Value should not be null but it is");
    }

    /**
     * Test 3: This test will pass - Array manipulation
     */
    public function testArrayManipulationPass()
    {
        $arr = [1, 2, 3];
        $this->assertCount(3, $arr, "Array should have 3 elements");
    }

    /**
     * Test 4: This test will fail - Type checking
     */
    public function testTypeCheckingFail()
    {
        $number = "123";
        $this->assertIsInt($number, "Expected integer but got string");
    }

    /**
     * Test 5: This test will pass - String operations
     */
    public function testStringOperationsPass()
    {
        $str = "Hello World";
        $this->assertStringStartsWith('Hello', $str, "String should start with Hello");
    }

    /**
     * Test 6: This test will fail - Boundary condition
     */
    public function testBoundaryConditionFail()
    {
        $age = 150;
        $this->assertLessThan(120, $age, "Age cannot exceed 120 years");
    }

    /**
     * Test 7: This test will pass - Boolean logic
     */
    public function testBooleanLogicPass()
    {
        $isActive = true;
        $this->assertTrue($isActive, "Should be active");
    }

    /**
     * Test 8: This test will fail - Regular expression
     */
    public function testRegexMatchFail()
    {
        $email = "test@example";
        $this->assertMatchesRegularExpression('/^[\w\-\.]+@[\w\-]+\.\w+$/', $email, "Email format invalid");
    }

    /**
     * Test 9: This test will pass - Exception handling
     */
    public function testExceptionHandlingPass()
    {
        $this->expectNotToPerformAssertions();
        // No exception thrown
    }

    /**
     * Test 10: This test will fail - Empty array check
     */
    public function testEmptyArrayFail()
    {
        $data = [];
        $this->assertNotEmpty($data, "Data array should not be empty");
    }
}
