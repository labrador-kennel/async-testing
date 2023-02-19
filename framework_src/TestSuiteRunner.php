<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Amp\Promise;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncEvent\StandardEvent;
use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Context\ExpectationContext;
use Cspray\Labrador\AsyncUnit\Event\TestCaseFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestCaseStartedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestDisabledEvent;
use Cspray\Labrador\AsyncUnit\Event\TestErroredEvent;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Event\ProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\ProcessingStartedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteStartedEvent;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestCaseTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestDisabledException;
use Cspray\Labrador\AsyncUnit\Exception\TestErrorException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestSetupException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteSetUpException;
use Cspray\Labrador\AsyncUnit\Exception\TestSuiteTearDownException;
use Cspray\Labrador\AsyncUnit\Exception\TestTearDownException;
use Cspray\Labrador\AsyncUnit\Model\HookModel;
use Cspray\Labrador\AsyncUnit\Model\TestCaseModel;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use Cspray\Labrador\AsyncUnit\Model\TestSuiteModel;
use Cspray\Labrador\AsyncUnit\Parser\ParserResult;
use Cspray\Labrador\AsyncUnit\Statistics\ProcessedSummaryBuilder;
use ReflectionClass;
use SebastianBergmann\Timer\Duration;
use SebastianBergmann\Timer\Timer;
use Throwable;
use function Amp\call;

/**
 * @internal
 */
final class TestSuiteRunner {

    private array $reflectionCache = [];

    private ?string $mockBridgeClass = null;

    public function __construct(
        private EventEmitter $emitter,
        private CustomAssertionContext $customAssertionContext,
        private Randomizer $randomizer,
        private MockBridgeFactory $mockBridgeFactory
    ) {}

    public function setMockBridgeClass(?string $mockBridge) : void {
        $this->mockBridgeClass = $mockBridge;
    }

    public function runTestSuites(ParserResult $parserResult) : Promise {
        return call(function() use($parserResult) {
            yield $this->emitter->emit(
                new ProcessingStartedEvent($parserResult->getAggregateSummary())
            );

            $testSuiteModels = $this->randomizer->randomize($parserResult->getTestSuiteModels());

            $aggregateSummaryBuilder = new ProcessedSummaryBuilder();
            $aggregateSummaryBuilder->startProcessing();

            foreach ($testSuiteModels as $testSuiteModel) {
                $testSuiteClass = $testSuiteModel->getClass();
                /** @var TestSuite $testSuite */
                $testSuite = (new ReflectionClass($testSuiteClass))->newInstanceWithoutConstructor();
                $testSuiteSummary = $parserResult->getTestSuiteSummary($testSuite::class);
                yield $this->emitter->emit(new TestSuiteStartedEvent($testSuiteSummary));

                $aggregateSummaryBuilder->startTestSuite($testSuiteModel);
                if (!$testSuiteModel->isDisabled()) {
                    yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::BeforeAll, TestSuiteSetUpException::class);
                }

                /** @var TestCaseModel[] $testCaseModels */
                $testCaseModels = $this->randomizer->randomize($testSuiteModel->getTestCaseModels());
                foreach ($testCaseModels as $testCaseModel) {
                    $testCaseSummary = $parserResult->getTestCaseSummary($testCaseModel->getClass());
                    yield $this->emitter->emit(new TestCaseStartedEvent($testCaseSummary));

                    $aggregateSummaryBuilder->startTestCase($testCaseModel);
                    if (!$testSuiteModel->isDisabled()) {
                        yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::BeforeEach, TestSuiteSetUpException::class);
                    }
                    if (!$testCaseModel->isDisabled()) {
                        yield $this->invokeHooks($testCaseModel->getClass(), $testCaseModel, HookType::BeforeAll, TestCaseSetUpException::class, [$testSuite]);
                    }

                    $testMethodModels = $this->randomizer->randomize($testCaseModel->getTestModels());
                    foreach ($testMethodModels as $testMethodModel) {
                        /** @var AssertionContext $assertionContext */
                        /** @var AsyncAssertionContext $asyncAssertionContext */
                        [
                            $testCase,
                            $assertionContext,
                            $asyncAssertionContext,
                            $expectationContext,
                            $mockBridge
                        ] = $this->invokeTestCaseConstructor($testCaseModel->getClass(), $testSuite, $testMethodModel);
                        if ($testMethodModel->getDataProvider() !== null) {
                            $dataProvider = $testMethodModel->getDataProvider();
                            $dataSets = $testCase->$dataProvider();
                            foreach ($dataSets as $label => $args) {
                                yield $this->invokeTest(
                                    $aggregateSummaryBuilder,
                                    $testCase,
                                    $assertionContext,
                                    $asyncAssertionContext,
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
                                    $asyncAssertionContext,
                                    $expectationContext,
                                    $mockBridge
                                ] = $this->invokeTestCaseConstructor($testCaseModel->getClass(), $testSuite, $testMethodModel);
                            }
                        } else {
                            yield $this->invokeTest(
                                $aggregateSummaryBuilder,
                                $testCase,
                                $assertionContext,
                                $asyncAssertionContext,
                                $expectationContext,
                                $mockBridge,
                                $testSuiteModel,
                                $testCaseModel,
                                $testMethodModel
                            );
                        }
                    }

                    if (!$testCaseModel->isDisabled()) {
                        yield $this->invokeHooks($testCaseModel->getClass(), $testCaseModel, HookType::AfterAll, TestCaseTearDownException::class, [$testSuite]);
                    }
                    if (!$testSuiteModel->isDisabled()) {
                        yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::AfterEach, TestSuiteTearDownException::class);
                    }
                    yield $this->emitter->emit(new TestCaseFinishedEvent($aggregateSummaryBuilder->finishTestCase($testCaseModel)));
                    ;
                }

                if (!$testSuiteModel->isDisabled()) {
                    yield $this->invokeHooks($testSuite, $testSuiteModel, HookType::AfterAll, TestSuiteTearDownException::class);
                }
                yield $this->emitter->emit(new TestSuiteFinishedEvent($aggregateSummaryBuilder->finishTestSuite($testSuiteModel)));
            }


            yield $this->emitter->emit(
                new ProcessingFinishedEvent($aggregateSummaryBuilder->finishProcessing())
            );
        });
    }

    private function invokeHooks(
        TestSuite|TestCase|string $hookTarget,
        TestSuiteModel|TestCaseModel $model,
        HookType $hookType,
        string $exceptionType,
        array $args = []
    ) : Promise {
        return call(function() use($hookTarget, $model, $hookType, $exceptionType, $args) {
            $hooks = $model->getHooks($hookType);
            usort($hooks, fn(HookModel $one, HookModel $two) => $one->getPriority() <=> $two->getPriority());
            foreach ($hooks as $hookMethodModel) {
                try {
                    yield call([$hookTarget, $hookMethodModel->getMethod()], ...$args);
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
        });
    }

    private function invokeTest(
        ProcessedSummaryBuilder $aggregateSummaryBuilder,
        TestCase $testCase,
        AssertionContext $assertionContext,
        AsyncAssertionContext $asyncAssertionContext,
        ExpectationContext $expectationContext,
        ?MockBridge $mockBridge,
        TestSuiteModel $testSuiteModel,
        TestCaseModel $testCaseModel,
        TestModel $testModel,
        array $args = [],
        ?string $dataSetLabel = null
    ) : Promise {
        return call(function() use(
            $aggregateSummaryBuilder,
            $testCase,
            $assertionContext,
            $asyncAssertionContext,
            $expectationContext,
            $mockBridge,
            $testSuiteModel,
            $testCaseModel,
            $testModel,
            $args,
            $dataSetLabel
        ) {
            if ($testModel->isDisabled()) {
                $msg = $testModel->getDisabledReason() ??
                    $testCaseModel->getDisabledReason() ??
                    $testSuiteModel->getDisabledReason() ??
                    sprintf('%s::%s has been marked disabled via annotation', $testCaseModel->getClass(), $testModel->getMethod());
                $exception = new TestDisabledException($msg);
                $testResult = $this->getDisabledTestResult($testCase, $testModel->getMethod(), $exception);
                yield $this->emitter->emit(new TestProcessedEvent($testResult));
                yield $this->emitter->emit(new TestDisabledEvent($testResult));
                $aggregateSummaryBuilder->processedTest($testResult);
                return;
            }

            if (isset($mockBridge)) {
                $mockBridge->initialize();
            }

            yield $this->invokeHooks($testCase->testSuite(), $testSuiteModel, HookType::BeforeEachTest, TestSetupException::class);
            yield $this->invokeHooks($testCase, $testCaseModel, HookType::BeforeEach, TestSetupException::class);

            $testCaseMethod = $testModel->getMethod();
            $failureException = null;
            $timer = new Timer();
            $timer->start();
            $timeoutWatcherId = null;
            if (!is_null($testModel->getTimeout())) {
                $timeoutWatcherId = Loop::delay($testModel->getTimeout(), function() use(&$timeoutWatcherId, $testModel) {
                    Loop::cancel($timeoutWatcherId);
                    $msg = sprintf(
                        'Expected %s::%s to complete within %sms',
                        $testModel->getClass(),
                        $testModel->getMethod(),
                        $testModel->getTimeout()
                    );
                    throw new TestFailedException($msg);
                });
            }
            Loop::setErrorHandler(function(Throwable $error) use(&$failureException, $expectationContext) {
                if ($error instanceof TestFailedException) {
                    $failureException = $error;
                } else {
                    $expectationContext->setThrownException($error);
                }
            });
            try {
                ob_start();
                yield call(fn() => $testCase->$testCaseMethod(...$args));
            } catch (TestFailedException $exception) {
                $failureException = $exception;
            } catch (Throwable $throwable) {
                $expectationContext->setThrownException($throwable);
            } finally {
                Loop::setErrorHandler();
                if (isset($timeoutWatcherId)) {
                    Loop::cancel($timeoutWatcherId);
                }
                $expectationContext->setActualOutput(ob_get_clean());
                if (isset($mockBridge)) {
                    $assertionContext->addToAssertionCount($mockBridge->getAssertionCount());
                }
                // If something else failed we don't need to make validations about expectations
                if (is_null($failureException)) {
                    $failureException = yield $expectationContext->validateExpectations();
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

            yield $this->invokeHooks($testCase, $testCaseModel, HookType::AfterEach, TestTearDownException::class);
            yield $this->invokeHooks($testCase->testSuite(), $testSuiteModel, HookType::AfterEachTest, TestTearDownException::class);

            yield $this->emitter->emit(new TestProcessedEvent($testResult));

            if (TestState::Passed === $testResult->getState()) {
                yield $this->emitter->emit(new TestPassedEvent($testResult));
            } else if (TestState::Errored === $testResult->getState()) {
                yield $this->emitter->emit(new TestErroredEvent($testResult));
            } else {
                yield $this->emitter->emit(new TestFailedEvent($testResult));
            }

            $aggregateSummaryBuilder->processedTest($testResult);

            unset($testCase);
            unset($failureException);
            unset($testResult);
        });
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
        /** @var TestCase $testCaseObject */
        $reflectionClass = $this->getReflectionClass($testCaseClass);
        $testCaseObject = $reflectionClass->newInstanceWithoutConstructor();
        $reflectedAssertionContext = $this->getReflectionClass(AssertionContext::class);
        $reflectedAsyncAssertionContext = $this->getReflectionClass(AsyncAssertionContext::class);
        $reflectedExpectationContext = $this->getReflectionClass(ExpectationContext::class);
        $testCaseConstructor = $reflectionClass->getConstructor();
        $testCaseConstructor->setAccessible(true);

        $assertionContext = $reflectedAssertionContext->newInstanceWithoutConstructor();
        $assertionContextConstructor = $reflectedAssertionContext->getConstructor();
        $assertionContextConstructor->setAccessible(true);
        $assertionContextConstructor->invoke($assertionContext, $this->customAssertionContext);

        $asyncAssertionContext = $reflectedAsyncAssertionContext->newInstanceWithoutConstructor();
        $asyncAssertionContextConstructor = $reflectedAsyncAssertionContext->getConstructor();
        $asyncAssertionContextConstructor->setAccessible(true);
        $asyncAssertionContextConstructor->invoke($asyncAssertionContext, $this->customAssertionContext);

        $testMocker = null;
        if (isset($this->mockBridgeClass)) {
            $testMocker = $this->mockBridgeFactory->make($this->mockBridgeClass);
        }

        $expectationContext = $reflectedExpectationContext->newInstanceWithoutConstructor();
        $expectationContextConstructor = $reflectedExpectationContext->getConstructor();
        $expectationContextConstructor->setAccessible(true);
        $expectationContextConstructor->invoke($expectationContext, $testModel, $assertionContext, $asyncAssertionContext, $testMocker);

        $testCaseConstructor->invoke(
            $testCaseObject,
            $testSuite,
            $assertionContext,
            $asyncAssertionContext,
            $expectationContext,
            $testMocker
        );
        return [$testCaseObject, $assertionContext, $asyncAssertionContext, $expectationContext, $testMocker];
    }

}