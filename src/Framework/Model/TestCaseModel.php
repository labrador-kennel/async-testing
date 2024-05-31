<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Model;

final class TestCaseModel {

    use HookAware;
    use CanBeDisabledTrait;
    use CanHaveTimeoutTrait;

    private array $testModels = [];

    public function __construct(
        private string $testCaseClass,
        private ?string $testSuiteClass = null
    ) {}

    public function getTestSuiteClass() : ?string {
        return $this->testSuiteClass;
    }

    public function setTestSuiteClass(string $testSuiteClass) : void {
        $this->testSuiteClass = $testSuiteClass;
    }

    public function getNamespace() : string {
        $pieces = explode('\\', $this->getClass());
        array_pop($pieces);
        return implode('\\', $pieces);
    }

    public function getClass() : string {
        return $this->testCaseClass;
    }

    public function addTestModel(TestModel $testMethodModel) : void {
        $this->testModels[] = $testMethodModel;
    }

    /**
     * @return TestModel[]
     */
    public function getTestModels() : array {
        return $this->testModels;
    }

}