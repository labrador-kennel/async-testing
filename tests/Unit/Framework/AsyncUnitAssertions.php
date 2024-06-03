<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Labrador\AsyncUnit\Test\Unit\Framework\Constraint\TestCaseModelHasTestMethod;
use Labrador\AsyncUnit\Test\Unit\Framework\Constraint\TestSuiteModelHasTestCaseModel;
use Labrador\AsyncUnit\Framework\Model\TestCaseModel;
use Labrador\AsyncUnit\Framework\Model\TestSuiteModel;

trait AsyncUnitAssertions {

    public function assertTestCaseClassBelongsToTestSuite(string $expected, TestSuiteModel $actual) {
        $constraint = new TestSuiteModelHasTestCaseModel($expected);
        $this->assertThat($actual, $constraint);
    }

    public function assertTestMethodBelongsToTestCase(string $methodSignature, TestCaseModel $actual) {
        [$testClass, $method] = explode('::', $methodSignature);
        $constraint = new TestCaseModelHasTestMethod($testClass, $method);
        $this->assertThat($actual, $constraint);
    }

}