<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

use Amp\Future;
use Labrador\AsyncEvent\Emitter;
use Labrador\AsyncUnit\Framework\Assertion\AssertionContext;
use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Framework\Context\ExpectationContext;
use Labrador\AsyncUnit\Framework\Event\ProcessingFinishedEvent;
use Labrador\AsyncUnit\Framework\Event\ProcessingStartedEvent;
use Labrador\AsyncUnit\Framework\Event\TestCaseFinishedEvent;
use Labrador\AsyncUnit\Framework\Event\TestCaseStartedEvent;
use Labrador\AsyncUnit\Framework\Event\TestDisabledEvent;
use Labrador\AsyncUnit\Framework\Event\TestErroredEvent;
use Labrador\AsyncUnit\Framework\Event\TestFailedEvent;
use Labrador\AsyncUnit\Framework\Event\TestPassedEvent;
use Labrador\AsyncUnit\Framework\Event\TestProcessedEvent;
use Labrador\AsyncUnit\Framework\Event\TestSuiteFinishedEvent;
use Labrador\AsyncUnit\Framework\Event\TestSuiteStartedEvent;
use Labrador\AsyncUnit\Framework\Exception\AssertionFailedException;
use Labrador\AsyncUnit\Framework\Exception\TestCaseSetUpException;
use Labrador\AsyncUnit\Framework\Exception\TestCaseTearDownException;
use Labrador\AsyncUnit\Framework\Exception\TestDisabledException;
use Labrador\AsyncUnit\Framework\Exception\TestErrorException;
use Labrador\AsyncUnit\Framework\Exception\TestFailedException;
use Labrador\AsyncUnit\Framework\Exception\TestSetupException;
use Labrador\AsyncUnit\Framework\Exception\TestSuiteSetUpException;
use Labrador\AsyncUnit\Framework\Exception\TestSuiteTearDownException;
use Labrador\AsyncUnit\Framework\Exception\TestTearDownException;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridge;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridgeFactory;
use Labrador\AsyncUnit\Framework\Model\HookModel;
use Labrador\AsyncUnit\Framework\Model\TestCaseModel;
use Labrador\AsyncUnit\Framework\Model\TestModel;
use Labrador\AsyncUnit\Framework\Model\TestSuiteModel;
use Labrador\AsyncUnit\Framework\Parser\ParserResult;
use Labrador\AsyncUnit\Framework\Randomizer\Randomizer;
use Labrador\AsyncUnit\Framework\Statistics\ProcessedSummaryBuilder;
use Labrador\CompositeFuture\CompositeFuture;
use ReflectionClass;
use Revolt\EventLoop;
use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\Timer;
use Throwable;

/**
 * @internal
 */
final class TestSuiteRunner {

    private array $reflectionCache = [];

    private ?string $mockBridgeClass = null;

    public function __construct(
        private readonly Emitter $emitter,
        private readonly Randomizer $randomizer,
        private readonly MockBridgeFactory $mockBridgeFactory
    ) {}

    public function setMockBridgeClass(?string $mockBridge) : void {
        $this->mockBridgeClass = $mockBridge;
    }

    public function runTestSuites(ParserResult $parserResult) : void {
        $this->emitter->emit(
            new ProcessingStartedEvent($parserResult->getAggregateSummary())
        )->awaitAll();

        $testSuiteModels = $this->randomizer->randomize($parserResult->getTestSuiteModels());

        $aggregateSummaryBuilder = new ProcessedSummaryBuilder();
        $aggregateSummaryBuilder->startProcessing();

        foreach ($testSuiteModels as $testSuiteModel) {
            $testSuiteClass = $testSuiteModel->getClass();
            $testSuite = new $testSuiteClass();
            $testSuiteSummary = $parserResult->getTestSuiteSummary($testSuite::class);
            $this->emitter->emit(new TestSuiteStartedEvent($testSuiteSummary));

            $aggregateSummaryBuilder->startTestSuite($testSuiteModel);
            if (!$testSuiteModel->isDisabled()) {
                $this->invokeHooks(
                    $testSuite,
                    $testSuiteModel,
                    HookType::BeforeAll,
                    TestSuiteSetUpException::class
                );
            }

            /** @var TestCaseModel[] $testCaseModels */
            $testCaseModels = $this->randomizer->randomize($testSuiteModel->getTestCaseModels());
            foreach ($testCaseModels as $testCaseModel) {
                $testCaseSummary = $parserResult->getTestCaseSummary($testCaseModel->getClass());
                $this->emitter->emit(new TestCaseStartedEvent($testCaseSummary))->awaitAll();

                $aggregateSummaryBuilder->startTestCase($testCaseModel);
                if (!$testSuiteModel->isDisabled()) {
                    $this->invokeHooks($testSuite, $testSuiteModel, HookType::BeforeEach, TestSuiteSetUpException::class);
                }
                if (!$testCaseModel->isDisabled()) {
                    $this->invokeHooks($testCaseModel->getClass(), $testCaseModel, HookType::BeforeAll, TestCaseSetUpException::class, [$testSuite]);
                }

                $testMethodModels = $this->randomizer->randomize($testCaseModel->getTestModels());
                foreach ($testMethodModels as $testMethodModel) {
                    /** @var AssertionContext $assertionContext */
                    [
                        $testCase,
                        $assertionContext,
                        $expectationContext,
                        $mockBridge
                    ] = $this->invokeTestCaseConstructor($testCaseModel->getClass(), $testSuite, $testMethodModel);
                    if ($testMethodModel->getDataProvider() !== null) {
                        $dataProvider = $testMethodModel->getDataProvider();
                        $dataSets = $testCase->$dataProvider();
                        foreach ($dataSets as $label => $args) {
                            $this->invokeTest(
                                $aggregateSummaryBuilder,
                                $testCase,
                                $assertionContext,
                                $expectationContext,
                                $mockBridge,
                                $testSuiteModel,
                                $testCaseModel,
                                $testMethodModel,
                                $args,
                                (string) $label // make sure 0-index array keys are treated as strings
                            );
                            [
                                $testCase,
                                $assertionContext,
                                $expectationContext,
                                $mockBridge
                            ] = $this->invokeTestCaseConstructor($testCaseModel->getClass(), $testSuite, $testMethodModel);
                        }
                    } else {
                        $this->invokeTest(
                            $aggregateSummaryBuilder,
                            $testCase,
                            $assertionContext,
                            $expectationContext,
                            $mockBridge,
                            $testSuiteModel,
                            $testCaseModel,
                            $testMethodModel
                        );
                    }
                }

                if (!$testCaseModel->isDisabled()) {
                    $this->invokeHooks($testCaseModel->getClass(), $testCaseModel, HookType::AfterAll, TestCaseTearDownException::class, [$testSuite]);
                }
                if (!$testSuiteModel->isDisabled()) {
                    $this->invokeHooks($testSuite, $testSuiteModel, HookType::AfterEach, TestSuiteTearDownException::class);
                }
                $this->emitter->emit(new TestCaseFinishedEvent($aggregateSummaryBuilder->finishTestCase($testCaseModel)));
            }

            if (!$testSuiteModel->isDisabled()) {
                $this->invokeHooks($testSuite, $testSuiteModel, HookType::AfterAll, TestSuiteTearDownException::class);
            }
            $this->emitter->emit(new TestSuiteFinishedEvent($aggregateSummaryBuilder->finishTestSuite($testSuiteModel)));
        }

        $this->emitter->emit(
            new ProcessingFinishedEvent($aggregateSummaryBuilder->finishProcessing())
        );
    }

    /**
     * @param class-string<Throwable> $exceptionType
     * @param list<mixed> $args
     * @throws Throwable
     */
    private function invokeHooks(
        TestSuite|TestCase|string $hookTarget,
        TestSuiteModel|TestCaseModel $model,
        HookType $hookType,
        string $exceptionType,
        array $args = []
    ) : void {
        $hooks = $model->getHooks($hookType);
        usort($hooks, static fn(HookModel $one, HookModel $two) => $one->getPriority() <=> $two->getPriority());
        foreach ($hooks as $hookMethodModel) {
            try {
                if (is_string($hookTarget)) {
                    $hookTarget::{$hookMethodModel->getMethod()}(...$args);
                } else {
                    $hookTarget->{$hookMethodModel->getMethod()}(...$args);
                }
            } catch (Throwable $throwable) {
                $hookTypeInflected = str_starts_with($hookType->value, 'Before') ? 'setting up' : 'tearing down';
                $msg = sprintf(
                    'Failed %s "%s::%s" #[%s] hook with exception of type "%s" with code %d and message "%s".',
                    $hookTypeInflected,
                    is_string($hookTarget) ? $hookTarget : $hookTarget::class,
                    $hookMethodModel->getMethod(),
                    $hookType->value,
                    $throwable::class,
                    $throwable->getCode(),
                    $throwable->getMessage()
                );
                throw new $exceptionType($msg, previous: $throwable);
            }
        }
    }

    private function invokeTest(
        ProcessedSummaryBuilder $aggregateSummaryBuilder,
        TestCase $testCase,
        AssertionContext $assertionContext,
        ExpectationContext $expectationContext,
        ?MockBridge $mockBridge,
        TestSuiteModel $testSuiteModel,
        TestCaseModel $testCaseModel,
        TestModel $testModel,
        array $args = [],
        ?string $dataSetLabel = null
    ) : void {
        if ($testModel->isDisabled()) {
            $msg = $testModel->getDisabledReason() ??
                $testCaseModel->getDisabledReason() ??
                $testSuiteModel->getDisabledReason() ??
                sprintf('%s::%s has been marked disabled via annotation', $testCaseModel->getClass(), $testModel->getMethod());
            $exception = new TestDisabledException($msg);
            $testResult = $this->getDisabledTestResult($testCase, $testModel->getMethod(), $exception);
            $this->emitter->emit(new TestProcessedEvent($testResult))->awaitAll();
            $this->emitter->emit(new TestDisabledEvent($testResult))->awaitAll();
            $aggregateSummaryBuilder->processedTest($testResult);
            return;
        }

        if (isset($mockBridge)) {
            $mockBridge->initialize();
        }

        $this->invokeHooks($testCase->testSuite, $testSuiteModel, HookType::BeforeEachTest, TestSetupException::class);
        $this->invokeHooks($testCase, $testCaseModel, HookType::BeforeEach, TestSetupException::class);

        $testCaseMethod = $testModel->getMethod();
        $failureException = null;
        $timer = new Timer();
        $timer->start();
        /** @var string|null $timeoutWatcherId */
        $timeoutWatcherId = null;
        if (!is_null($testModel->getTimeout())) {
            $timeoutWatcherId = EventLoop::delay($testModel->getTimeout() / 1000, static function() use(&$timeoutWatcherId, $testModel) {
                assert($timeoutWatcherId !== null);
                EventLoop::cancel($timeoutWatcherId);
                $msg = sprintf(
                    'Expected %s::%s to complete within %sms',
                    $testModel->getClass(),
                    $testModel->getMethod(),
                    $testModel->getTimeout()
                );
                throw new TestFailedException($msg);
            });
        }
        EventLoop::setErrorHandler(static function(Throwable $error) use(&$failureException, $expectationContext) {
            if ($error instanceof TestFailedException) {
                $failureException = $error;
            } else {
                $expectationContext->setThrownException($error);
            }
        });
        try {
            ob_start();
            $testReturn = $testCase->$testCaseMethod(...$args);
            if ($testReturn instanceof CompositeFuture) {
                $testReturn->awaitAll();
            } else if ($testReturn instanceof Future) {
                $testReturn->await();
            }
        } catch (TestFailedException $exception) {
            $failureException = $exception;
        } catch (Throwable $throwable) {
            $expectationContext->setThrownException($throwable);
        } finally {
            EventLoop::setErrorHandler(null);
            if (isset($timeoutWatcherId)) {
                EventLoop::cancel($timeoutWatcherId);
            }
            $expectationContext->setActualOutput(ob_get_clean());
            if (isset($mockBridge)) {
                $assertionContext->addToAssertionCount($mockBridge->getAssertionCount());
            }
            // If something else failed we don't need to make validations about expectations
            if (is_null($failureException)) {
                $failureException = $expectationContext->validateExpectations();
            }
            if (is_null($failureException)) {
                $state = TestState::Passed;
            } else if ($failureException instanceof TestFailedException) {
                $state = TestState::Failed;
            } else {
                $state = TestState::Errored;
            }
            $testResult = $this->getTestResult($testCase, $testCaseMethod, $state, $timer->stop(), $failureException, $dataSetLabel);
        }

        $this->invokeHooks($testCase, $testCaseModel, HookType::AfterEach, TestTearDownException::class);
        $this->invokeHooks($testCase->testSuite, $testSuiteModel, HookType::AfterEachTest, TestTearDownException::class);

        $this->emitter->emit(new TestProcessedEvent($testResult))->awaitAll();

        if (TestState::Passed === $testResult->getState()) {
            $this->emitter->emit(new TestPassedEvent($testResult));
        } else if (TestState::Errored === $testResult->getState()) {
            $this->emitter->emit(new TestErroredEvent($testResult));
        } else {
            $this->emitter->emit(new TestFailedEvent($testResult));
        }

        $aggregateSummaryBuilder->processedTest($testResult);

        unset($failureException, $testResult);
    }

    private function getReflectionClass(string $class) : ReflectionClass {
        if (!isset($this->reflectionCache[$class])) {
            $this->reflectionCache[$class] = new ReflectionClass($class);
        }

        return $this->reflectionCache[$class];
    }

    private function getDisabledTestResult(TestCase $testCase, string $testMethod, TestDisabledException $exception) : TestResult {
        return new class($testCase, $testMethod, $exception) implements TestResult {

            public function __construct(
                private TestCase $testCase,
                private string $testMethod,
                private TestDisabledException $exception
            ) {}

            public function getTestCase() : TestCase {
                return $this->testCase;
            }

            public function getTestMethod() : string {
                return $this->testMethod;
            }

            public function getDataSetLabel() : ?string {
                return null;
            }

            public function getState() : TestState {
                return TestState::Disabled;
            }

            public function getDuration() : Duration {
                return Duration::fromNanoseconds(0);
            }

            public function getException() : TestFailedException|AssertionFailedException|TestDisabledException|null {
                return $this->exception;
            }
        };
    }

    private function getTestResult(
        TestCase $testCase,
        string $method,
        TestState $state,
        Duration $duration,
        TestFailedException|TestErrorException|null $testFailedException,
        ?string $dataSetLabel
    ) : TestResult {
        return new class($testCase, $method, $state, $duration, $testFailedException, $dataSetLabel) implements TestResult {

            public function __construct(
                private TestCase $testCase,
                private string $method,
                private TestState $state,
                private Duration $duration,
                private TestFailedException|TestErrorException|null $testFailedException,
                private ?string $dataSetLabel
            ) {}

            public function getTestCase() : TestCase {
                return $this->testCase;
            }

            public function getTestMethod() : string {
                return $this->method;
            }

            public function getDataSetLabel() : ?string {
                return $this->dataSetLabel;
            }

            public function getState() : TestState {
                return $this->state;
            }

            public function getDuration() : Duration {
                return $this->duration;
            }

            public function getException() : TestFailedException|AssertionFailedException|TestDisabledException|TestErrorException|null {
                return $this->testFailedException;
            }
        };
    }

    private function invokeTestCaseConstructor(string $testCaseClass, TestSuite $testSuite, TestModel $testModel) : array {
        $assertionContext = new AssertionContext();
        $testMocker = null;
        if (isset($this->mockBridgeClass)) {
            $testMocker = $this->mockBridgeFactory->make($this->mockBridgeClass);
        }
        $expectationContext = new ExpectationContext(
            $testModel,
            $assertionContext,
            $testMocker
        );

        $testCase = new $testCaseClass(
            $testSuite,
            $assertionContext,
            $expectationContext,
            $testMocker
        );

        return [$testCase, $assertionContext, $expectationContext, $testMocker];
    }

}