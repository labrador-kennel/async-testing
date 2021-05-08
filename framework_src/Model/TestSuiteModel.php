<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Model;

final class TestSuiteModel {

    use HookAware;
    use CanBeDisabledTrait;

    private array $testCaseModels = [];

    public function __construct(
        private string $class,
        private bool $isDefaultTestSuite
    ) {}

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