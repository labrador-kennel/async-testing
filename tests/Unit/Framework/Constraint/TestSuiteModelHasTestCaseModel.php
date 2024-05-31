<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Constraint;

use Labrador\AsyncUnit\Framework\Exception\InvalidArgumentException;
use Labrador\AsyncUnit\Framework\Model\TestCaseModel;
use Labrador\AsyncUnit\Framework\Model\TestSuiteModel;
use PHPUnit\Framework\Constraint\Constraint;

class TestSuiteModelHasTestCaseModel extends Constraint {

    public function __construct(private string $expectedClass) {}

    protected function matches($other) : bool {
        if (!$other instanceof TestSuiteModel) {
            $msg = sprintf('You must pass a %s to %s', TestSuiteModel::class, self::class);
            throw new InvalidArgumentException($msg);
        }
        $testCases = $other->getTestCaseModels();
        if (empty($testCases)) {
            return false;
        }

        $testCaseClasses = array_map(static fn(TestCaseModel $model) => $model->getClass(), $testCases);
        return in_array($this->expectedClass, $testCaseClasses, true);
    }

    public function toString() : string {
        return sprintf('AttachToTestSuite has TestCase class "%s"', $this->expectedClass);
    }
}