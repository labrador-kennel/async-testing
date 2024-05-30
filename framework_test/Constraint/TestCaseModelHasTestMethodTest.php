<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Constraint;

use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use PHPUnit\Framework\TestCase;
use stdClass;

class TestCaseModelHasTestMethodTest extends TestCase {

    public function testPassingNonTestCaseThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'You must pass a %s to %s',
            TestCaseModel::class,
            TestCaseModelHasTestMethod::class
        ));

        (new TestCaseModelHasTestMethod('TestCaseClass', 'method'))->evaluate(new stdClass(), returnResult: true);
    }

    public function testPassingEmptyTestCaseIsFalse() {
        $testCaseModel = new TestCaseModel('FooClass');

        $result = (new TestCaseModelHasTestMethod('FooClass', 'method'))->evaluate($testCaseModel, returnResult: true);

        $this->assertFalse($result);
    }

    public function testPassingMethodBelongsToTestCaseIsTrue() {
        $testCaseModel = new TestCaseModel('FooClass');
        $testCaseModel->addTestModel(new TestModel('FooClass', 'method'));

        $result = (new TestCaseModelHasTestMethod('FooClass', 'method'))->evaluate($testCaseModel, returnResult: true);

        $this->assertTrue($result);
    }

    public function testPassingMethodNotBelongsToTestCaseIsFalse() {
        $testCaseModel = new TestCaseModel('BarClass');
        $testCaseModel->addTestModel(new TestModel('BarClass', 'ensureSomething'));

        $result = (new TestCaseModelHasTestMethod('BarClass', 'ensureSomethingElse'))->evaluate($testCaseModel, returnResult: true);

        $this->assertFalse($result);
    }

    public function testClassesDontMatchIsFalse() {
        $testCaseModel = new TestCaseModel('FooClass');
        $testCaseModel->addTestModel(new TestModel('FooClass', 'ensureSomething'));

        $result = (new TestCaseModelHasTestMethod('BarClass', 'ensureSomething'))->evaluate($testCaseModel, returnResult: true);

        $this->assertFalse($result);
    }

}