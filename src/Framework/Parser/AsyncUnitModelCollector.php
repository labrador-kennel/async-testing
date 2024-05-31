<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Parser;

use Labrador\AsyncUnit\Framework\Exception\TestCompilationException;
use Labrador\AsyncUnit\Framework\Model\HookModel;
use Labrador\AsyncUnit\Framework\Model\PluginModel;
use Labrador\AsyncUnit\Framework\Model\TestCaseModel;
use Labrador\AsyncUnit\Framework\Model\TestModel;
use Labrador\AsyncUnit\Framework\Model\TestSuiteModel;
use Labrador\AsyncUnit\Framework\TestSuite;

/**
 * @internal
 */
final class AsyncUnitModelCollector {

    private ?string $defaultTestSuite = null;

    /**
     * @var array<class-string<TestSuite>, TestSuiteModel>
     */
    private array $testSuiteModels = [];

    /**
     * @var TestCaseModel[]
     */
    private array $testCaseModels = [];

    /**
     * @var TestModel[]
     */
    private array $testModels = [];

    /**
     * @var HookModel[]
     */
    private array $hookModels = [];

    /**
     * @var PluginModel[]
     */
    private array $pluginModels= [];

    public function attachTestSuite(TestSuiteModel $testSuiteModel) : void {
        if ($testSuiteModel->isDefaultTestSuite()) {
            $this->defaultTestSuite = $testSuiteModel->getClass();
        }
        $this->testSuiteModels[$testSuiteModel->getClass()] = $testSuiteModel;
    }

    public function attachTestCase(TestCaseModel $testCaseModel) : void {
        $this->testCaseModels[] = $testCaseModel;
    }

    public function attachTest(TestModel $testModel) : void {
        $this->testModels[] = $testModel;
    }

    public function attachHook(HookModel $hookModel) : void {
        $this->hookModels[] = $hookModel;
    }

    public function attachPlugin(PluginModel $pluginModel) : void {
        $this->pluginModels[] = $pluginModel;
    }

    public function hasDefaultTestSuite() : bool {
        return isset($this->defaultTestSuite);
    }

    /**
     * @return list<TestSuiteModel>
     */
    public function getTestSuiteModels() : array {
        return array_values($this->testSuiteModels);
    }

    public function getPluginModels() : array {
        return $this->pluginModels;
    }

    public function finishedCollection() : void {
        foreach ($this->hookModels as $hookModel) {
            foreach (array_merge([], $this->testSuiteModels, $this->testCaseModels) as $model) {
                if ($hookModel->getClass() === $model->getClass()) {
                    $model->addHook($hookModel);
                    continue 2;
                }
            }
        }

        foreach ($this->testCaseModels as $testCaseModel) {
            // This could potentially be set with the AttachToTestSuite attribute inside the node visitor
            // We should only adjust the test suite if the test case did not explicitly define one
            if (is_null($testCaseModel->getTestSuiteClass())) {
                // Before we assign the default test suite we need to check if any test suites have an attach namespace
                // defined that matches the given test case namespace.
                $testCaseTestSuite = null;
                $testCaseNamespace = $testCaseModel->getNamespace();
                /** @var class-string<TestSuite> $testSuiteClass */
                foreach (array_keys($this->testSuiteModels) as $testSuiteClass) {
                    $testSuiteAttachNamespaces = $testSuiteClass::getNamespacesToAttach();
                    foreach ($testSuiteAttachNamespaces as $testSuiteAttachNamespace) {
                        if (preg_match('#' . $testSuiteAttachNamespace . '#', $testCaseNamespace) === 1) {
                            $testCaseTestSuite = $testSuiteClass;
                        }
                    }
                }
                $testCaseModel->setTestSuiteClass($testCaseTestSuite ?? $this->defaultTestSuite);
            }

            $testSuiteModel = $this->testSuiteModels[$testCaseModel->getTestSuiteClass()];
            if ($testSuiteModel->isDisabled()) {
                $testCaseModel->markDisabled($testSuiteModel->getDisabledReason());
            }
            if (!is_null($testSuiteModel->getTimeout())) {
                $testCaseModel->setTimeout($testSuiteModel->getTimeout());
            }
            $testSuiteModel->addTestCaseModel($testCaseModel);
            foreach ($this->testModels as $testModel) {
                $testClass = $testModel->getClass();
                if ($testCaseModel->getClass() === $testClass || is_subclass_of($testCaseModel->getClass(), $testClass)) {
                    $testCaseTest = $testModel->withClass($testCaseModel->getClass());
                    if ($testCaseModel->isDisabled()) {
                        $testCaseTest->markDisabled($testCaseModel->getDisabledReason());
                    }
                    $testCaseTimeout = $testCaseModel->getTimeout();
                    if (!is_null($testCaseTimeout)) {
                        $testCaseTest->setTimeout($testCaseTimeout);
                    }
                    $testCaseModel->addTestModel($testCaseTest);
                }
            }
            if (empty($testCaseModel->getTestModels())) {
                $msg = sprintf(
                    'Failure compiling "%s". There were no #[Test] found.',
                    $testCaseModel->getClass()
                );
                throw new TestCompilationException($msg);
            }
        }

        if (empty($this->testSuiteModels[$this->defaultTestSuite]->getTestCaseModels())) {
            unset($this->testSuiteModels[$this->defaultTestSuite]);
        }

        unset($this->testModels);
    }

}
