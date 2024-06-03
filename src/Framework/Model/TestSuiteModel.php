<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Model;

use Labrador\AsyncUnit\Framework\TestSuite;

final class TestSuiteModel {

    use HookAware;
    use CanBeDisabledTrait;
    use CanHaveTimeoutTrait;

    private array $testCaseModels = [];

    public function __construct(
        private string $class,
        private bool $isDefaultTestSuite
    ) {}

    /**
     * @return class-string<TestSuite>
     */
    public function getClass() : string {
        return $this->class;
    }

    public function isDefaultTestSuite() : bool {
        return $this->isDefaultTestSuite;
    }

    /**
     * @return TestCaseModel[]
     */
    public function getTestCaseModels() : array {
        return $this->testCaseModels;
    }

    public function addTestCaseModel(TestCaseModel $testCaseModel) : void {
        $this->testCaseModels[] = $testCaseModel;
    }

}