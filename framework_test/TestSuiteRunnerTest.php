<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Amp\Promise;
use Amp\Success;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use Cspray\Labrador\AsyncUnit\Exception\MockFailureException;
use Cspray\Labrador\AsyncUnit\Exception\TestDisabledException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestOutputException;
use Cspray\Labrador\AsyncUnit\Event\TestProcessedEvent;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use Cspray\Labrador\AsyncUnit\Stub\FailingMockBridgeStub;
use Cspray\Labrador\AsyncUnit\Stub\MockBridgeStub;
use Exception;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use function Amp\call;

class TestSuiteRunnerTest extends PHPUnitTestCase {

    use UsesAcmeSrc;
    use TestSuiteRunnerScaffolding;

    /**
     * @var TestResult[]
     */
    private array $actual = [];

    public function setUp() : void {
        $this->buildTestSuiteRunner();
        $this->emitter->on(Events::TEST_PROCESSED, function(TestProcessedEvent $event) {
            $this->actual[] = $event->getTarget();
        });
    }

    public function tearDown(): void {
        ImplicitDefaultTestSuite\TestCaseHooksPriority\MyTestCase::clearInvokedAll();
    }

    private function parseAndRun(string $path) : Promise {
        return call(function() use($path) {
            $results = yield $this->parser->parse($path);
            yield $this->testSuiteRunner->runTestSuites($results);
        });
    }

    public function testImplicitDefaultTestSuiteSingleTestEmitsTestProcessedEventWithProperTestCaseInstance() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

            $this->assertCount(1, $this->actual);
            $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $this->actual[0]->getTestCase());
        });
    }

    public function testImplicitDefaultTestSuiteSingleTestEmitsTestProcessedEventWithProperTestMethodName() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

            $this->assertCount(1, $this->actual);
            $this->assertSame('ensureSomethingHappens', $this->actual[0]->getTestMethod());
        });
    }

    public function testImplicitDefaultTestSuiteSingleTestEmitsTestProcessedEventWithInvokedTestCase() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

            $this->assertCount(1, $this->actual);
            $this->assertTrue($this->actual[0]->getTestCase()->getTestInvoked());
        });
    }

    public function testImplicitDefaultTestSuiteMultipleTestEmitsTestProcessedEventsEachTestUniqueTestCase() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('MultipleTest'));
            $this->assertCount(3, $this->actual);

            $actual = [
                $this->actual[0]->getTestCase()->getInvokedMethods(),
                $this->actual[1]->getTestCase()->getInvokedMethods(),
                $this->actual[2]->getTestCase()->getInvokedMethods()
            ];
            $expected = [
                [ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappens'],
                [ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappensTwice'],
                [ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappensThreeTimes']
            ];

            $this->assertEqualsCanonicalizing($expected, $actual);
        });
    }

    public function testImplicitDefaultTestSuiteHasSingleBeforeAllHookInvokedBeforeTest() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('HasSingleBeforeAllHook'));

            $this->assertCount(2, $this->actual);

            $actual = [
                $this->actual[0]->getTestCase()->getCombinedData(),
                $this->actual[1]->getTestCase()->getCombinedData()
            ];
            $expected = [
                ['beforeAll', 'ensureSomething'],
                ['beforeAll', 'ensureSomethingTwice']
            ];

            $this->assertEqualsCanonicalizing($expected, $actual);
        });
    }

    public function testImplicitDefaultTestSuiteHasSingleBeforeEachHookInvokedBeforeTest() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('HasSingleBeforeEachHook'));

            $this->assertCount(2, $this->actual);
            $actual = [
                $this->actual[0]->getTestCase()->getData(),
                $this->actual[1]->getTestCase()->getData()
            ];
            $expected = [
                ['beforeEach', 'ensureSomething'],
                ['beforeEach', 'ensureSomethingTwice']
            ];
            $this->assertEqualsCanonicalizing($expected, $actual);
        });
    }

    public function testImplicitDefaultTestSuiteHasSingleAfterAllHookInvokedAfterTest() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('HasSingleAfterAllHook'));

            $this->assertCount(2, $this->actual);
            $actual = [
                $this->actual[0]->getTestCase()->getCombinedData(),
                $this->actual[1]->getTestCase()->getCombinedData(),
            ];
            // We expect the afterAll _first_ here because our test case combines the class data from AfterAll and the object
            // data from the TestCase with class data first.
            $expected = [
                ['afterAll', 'ensureSomething'],
                ['afterAll', 'ensureSomethingTwice']
            ];
            $this->assertEqualsCanonicalizing($expected, $actual);
        });
    }

    public function testImplicitDefaultTestSuiteHasSingleAfterEachHookInvokedAfterTest() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('HasSingleAfterEachHook'));

            $this->assertCount(2, $this->actual);
            $actual = [
                $this->actual[0]->getTestCase()->getData(),
                $this->actual[1]->getTestCase()->getData()
            ];
            $expected = [
                ['ensureSomething', 'afterEach'],
                ['ensureSomethingTwice', 'afterEach']
            ];

            $this->assertEqualsCanonicalizing($expected, $actual);
        });
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingTestEmitsTestProcessedEventWithErrorStateAndCorrectException() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('ExceptionThrowingTest'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(TestState::Errored, $this->actual[0]->getState());

            $this->assertNotNull($this->actual[0]->getException());
            $expectedMsg = 'An unexpected exception of type "Exception" with code 0 and message "Test failure" was thrown from #[Test] ' . ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class . '::throwsException';
            $this->assertSame($expectedMsg, $this->actual[0]->getException()->getMessage());
            $this->assertInstanceOf(Exception::class, $this->actual[0]->getException()->getPrevious());
        });
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingTestWithAfterEachHookInvokedAfterTest() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('ExceptionThrowingTestWithAfterEachHook'));

            $this->assertCount(1, $this->actual);
            $this->assertTrue($this->actual[0]->getTestCase()->getAfterHookCalled());
        });
    }

    public function testImplicitDefaultTestSuiteTestFailedExceptionThrowingTestEmitsTestProcessedEventDoesNotMarkExceptionAsUnexpected() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestFailedExceptionThrowingTest'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(TestState::Failed, $this->actual[0]->getState());

            $this->assertNotNull($this->actual[0]->getException());
            $this->assertSame('Something barfed', $this->actual[0]->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteCustomAssertionsEmitsTestProcessedEventWithCorrectData() {
        Loop::run(function() {
            // Mock setup to make sure our custom assertion is being called properly
            $assertion = $this->getMockBuilder(Assertion::class)->getMock();
            $assertionResult = $this->getMockBuilder(AssertionResult::class)->getMock();
            $assertionResult->expects($this->once())->method('isSuccessful')->willReturn(true);
            $assertion->expects($this->once())->method('assert')->willReturn($assertionResult);

            $asyncAssertion = $this->getMockBuilder(AsyncAssertion::class)->getMock();
            $asyncAssertionResult = $this->getMockBuilder(AssertionResult::class)->getMock();
            $asyncAssertionResult->expects($this->once())->method('isSuccessful')->willReturn(true);
            $asyncAssertion->expects($this->once())->method('assert')->willReturn(new Success($asyncAssertionResult));

            $this->customAssertionContext->registerAssertion('theCustomAssertion', fn() => $assertion);
            $this->customAssertionContext->registerAsyncAssertion('theCustomAssertion', fn() => $asyncAssertion);

            // Normal TestSuiteRunner testing
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('CustomAssertions'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(TestState::Passed, $this->actual[0]->getState());

        });
    }

    public function testImplicitDefaultTestSuiteHasDataProviderEmitsTestProcessedEventsForEachDataSetOnUniqueTestCase() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('HasDataProvider'));
            $this->assertCount(3, $this->actual);

            $actual = [
                $this->actual[0]->getTestCase()->getCounter(),
                $this->actual[1]->getTestCase()->getCounter(),
                $this->actual[2]->getTestCase()->getCounter(),
            ];
            $expected = [1, 1, 1];

            $this->assertEqualsCanonicalizing($expected, $actual);
        });
    }

    public function testExplicitTestSuiteDefaultExplicitTestSuite() {
        Loop::run(function() {
            yield $this->parseAndRun($this->explicitTestSuitePath('AnnotatedDefaultTestSuite'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(ExplicitTestSuite\AnnotatedDefaultTestSuite\MyTestSuite::class, $this->actual[0]->getTestCase()->testSuite()::class);
        });
    }

    public function testImplicitDefaultTestSuiteMultipleBeforeAllHooksAllInvokedBeforeTest() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('MultipleBeforeAllHooks'));
            $this->assertCount(2, $this->actual);
            $actual = [
                $this->actual[0]->getTestCase()->getState(),
                $this->actual[1]->getTestCase()->getState(),
            ];
            $expected = [
                ImplicitDefaultTestSuite\MultipleBeforeAllHooks\FirstTestCase::class,
                ImplicitDefaultTestSuite\MultipleBeforeAllHooks\SecondTestCase::class
            ];
            $this->assertEqualsCanonicalizing($expected, $actual);
        });
    }

    public function testExplicitTestSuiteBeforeAllTestSuiteHookTestCaseHasAccessToSameTestSuite() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->explicitTestSuitePath('BeforeAllTestSuiteHook'));
            $this->assertCount(3, $this->actual);
            $actual = [
                $this->actual[0]->getTestCase()->testSuite(),
                $this->actual[1]->getTestCase()->testSuite(),
                $this->actual[2]->getTestCase()->testSuite(),
            ];
            $this->assertSame($actual[0], $actual[1]);
            $this->assertSame($actual[1], $actual[2]);
        });
    }

    public function testTestPassedEventsEmittedAfterTestProcessedEvent() {
        Loop::run(function() {
            $actual = [];
            $this->emitter->on(Events::TEST_PROCESSED, function() use(&$actual) {
                $actual[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_PASSED, function() use(&$actual) {
                $actual[] = 'test passed';
            });
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

            $this->assertSame(['test invoked', 'test passed'], $actual);
        });
    }

    public function testTestFailedEventsEmittedAfterTestProcessedEvent() {
        Loop::run(function() {
            $actual = [];
            $this->emitter->on(Events::TEST_PROCESSED, function() use(&$actual) {
                $actual[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_FAILED, function() use(&$actual) {
                $actual[] = 'test failed';
            });
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('FailedAssertion'));

            $this->assertSame(['test invoked', 'test failed'], $actual);
        });
    }

    public function testTestErrorEventEmittedAfterTestProcessedEvent() {
        Loop::run(function() {
            $actual = [];
            $this->emitter->on(Events::TEST_PROCESSED, function() use(&$actual) {
                $actual[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_ERRORED, function() use(&$actual) {
                $actual[] = 'test error';
            });
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('ExceptionThrowingTest'));

            $this->assertSame(['test invoked', 'test error'], $actual);
        });
    }

    public function testTestDisabledEventsEmittedAfterTestProcessedEvent() {
        Loop::run(function() {
            $actual = [];
            $this->emitter->on(Events::TEST_PROCESSED, function() use(&$actual) {
                $actual[] = 'test invoked';
            });
            $this->emitter->on(Events::TEST_DISABLED, function() use(&$actual) {
                $actual[] = 'test disabled';
            });
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTestDisabled'));

            $this->assertSame(['test invoked', 'test disabled'], $actual);
        });
    }

    public function testTestSuiteStartedAndFinishedEventsEmittedInOrder() {
        Loop::run(function() {
            $actual = [];
            $this->emitter->on(Events::TEST_SUITE_STARTED, function() use(&$actual) {
                $actual[] = 'test suite start';
            });
            $this->emitter->on(Events::TEST_PROCESSED, function() use(&$actual) {
                $actual[] = 'test processed';
            });
            $this->emitter->on(Events::TEST_SUITE_FINISHED, function() use(&$actual) {
                $actual[] = 'test finished';
            });
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

            $this->assertSame(['test suite start', 'test processed', 'test finished'], $actual);
        });
    }

    public function testTestCaseProcessingEventEmitted() {
        Loop::run(function() {
            $actual = [];
            $this->emitter->on(Events::TEST_CASE_STARTED, function() use(&$actual) {
                $actual[] = 'test case started';
            });
            $this->emitter->on(Events::TEST_PROCESSED, function() use(&$actual) {
                $actual[] = 'test processed';
            });
            $this->emitter->on(Events::TEST_CASE_FINISHED, function() use(&$actual) {
                $actual[] = 'test case finished';
            });

            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

            $this->assertSame(['test case started', 'test processed', 'test case finished'], $actual);
        });
    }

    public function testTestMethodIsNotInvokedWhenDisabled() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabled'));

            $this->assertCount(2, $this->actual);
            $actual = [
                $this->actual[0]->getState(),
                $this->actual[1]->getState()
            ];
            $expected = [TestState::Passed, TestState::Disabled];
            $this->assertEqualsCanonicalizing($expected, $actual);
        });
    }

    public function testTestMethodIsNotInvokedWhenTestCaseDisabled() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseDisabled'));

            $this->assertCount(3, $this->actual);
            $actualState = [
                $this->actual[0]->getState(),
                $this->actual[1]->getState(),
                $this->actual[2]->getState(),
            ];
            $expectedState = [TestState::Disabled, TestState::Disabled, TestState::Disabled];
            $this->assertEqualsCanonicalizing($expectedState, $actualState);

            $actualData = [
                $this->actual[0]->getTestCase()->getData(),
                $this->actual[1]->getTestCase()->getData(),
                $this->actual[2]->getTestCase()->getData(),
            ];
            $expectedData = [[], [], []];
            $this->assertEqualsCanonicalizing($expectedData, $actualData);
        });
    }

    public function testTestResultWhenTestDisabled() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabled'));
            $disabledTestResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class, 'skippedTest');

            $this->assertSame(TestState::Disabled, $disabledTestResult->getState());
            $this->assertInstanceOf(TestDisabledException::class, $disabledTestResult->getException());
            $expected = sprintf(
                '%s::%s has been marked disabled via annotation',
                ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class,
                'skippedTest'
            );
            $this->assertSame($expected, $disabledTestResult->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteHandleNonPhpFiles() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('HandleNonPhpFiles'));

            $this->assertCount(1, $this->actual);
        });
    }

    public function testImplicitDefaultTestSuiteTestDisabledHookNotInvoked() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabledHookNotInvoked'));

            $disabledTestResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledHookNotInvoked\MyTestCase::class, 'disabledTest');

            $this->assertSame(TestState::Disabled, $disabledTestResult->getState());
            $this->assertSame([], $disabledTestResult->getTestCase()->getState());

            $enabledTestResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledHookNotInvoked\MyTestCase::class, 'enabledTest');

            $this->assertSame(TestState::Passed, $enabledTestResult->getState());
            $this->assertSame(['before', 'enabled', 'after'], $enabledTestResult->getTestCase()->getState());
        });
    }

    public function testImplicitDefaultTestSuiteTestCaseDisabledHookNotInvoked() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseDisabledHookNotInvoked'));

            $testOneResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestCaseDisabledHookNotInvoked\MyTestCase::class, 'testOne');

            $this->assertSame(TestState::Disabled, $testOneResult->getState());
            $this->assertSame([], $testOneResult->getTestCase()->getState());

            $testTwoResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestCaseDisabledHookNotInvoked\MyTestCase::class, 'testTwo');

            $this->assertSame(TestState::Disabled, $testTwoResult->getState());
            $this->assertSame([], $testTwoResult->getTestCase()->getState());
        });
    }

    public function testExplicitTestSuiteTestSuiteDisabledHookNotInvoked() {
        Loop::run(function() {
            yield $this->parseAndRun($this->explicitTestSuitePath('TestSuiteDisabledHookNotInvoked'));

            $testSomethingResult = $this->fetchTestResultForTest(ExplicitTestSuite\TestSuiteDisabledHookNotInvoked\MyTestCase::class, 'testSomething');

            $this->assertSame(TestState::Disabled, $testSomethingResult->getState());
            $this->assertSame([], $testSomethingResult->getTestCase()->testSuite()->getState());
        });
    }

    public function testImplicitDefaultTestSuiteTestDisabledCustomMessage() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabledCustomMessage'));

            $testOneResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledCustomMessage\MyTestCase::class, 'testOne');

            $this->assertSame(TestState::Disabled, $testOneResult->getState());
            $this->assertInstanceOf(TestDisabledException::class, $testOneResult->getException());
            $this->assertSame('Not sure what we should do here yet', $testOneResult->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestCaseDisabledCustomMessage() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseDisabledCustomMessage'));

            $testOneResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestCaseDisabledCustomMessage\MyTestCase::class, 'testOne');

            $this->assertSame(TestState::Disabled, $testOneResult->getState());
            $this->assertInstanceOf(TestDisabledException::class, $testOneResult->getException());
            $this->assertSame('The TestCase is disabled', $testOneResult->getException()->getMessage());
        });
    }

    public function testExplicitTestSuiteTestSuiteDisabledCustomMessage() {
        Loop::run(function() {
            yield $this->parseAndRun($this->explicitTestSuitePath('TestSuiteDisabledCustomMessage'));

            $testOneResult = $this->fetchTestResultForTest(ExplicitTestSuite\TestSuiteDisabledCustomMessage\MyTestCase::class, 'testOne');

            $this->assertSame(TestState::Disabled, $testOneResult->getState());
            $this->assertInstanceOf(TestDisabledException::class, $testOneResult->getException());
            $this->assertSame('The AttachToTestSuite is disabled', $testOneResult->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestEventsHaveCorrectState() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabledEvents'));

            $failingResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledEvents\MyTestCase::class, 'testFailingFloatEquals');

            $this->assertSame(TestState::Failed, $failingResult->getState());

            $passingResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledEvents\MyTestCase::class, 'testIsTrue');

            $this->assertSame(TestState::Passed, $passingResult->getState());

            $disabledResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledEvents\MyTestCase::class, 'testIsDisabled');

            $this->assertSame(TestState::Disabled, $disabledResult->getState());
        });
    }

    public function testImplicitDefaultTestSuiteTestHasOutput() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestHasOutput'));

            $failingResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestHasOutput\MyTestCase::class, 'testProducesOutput');

            $this->assertInstanceOf(TestOutputException::class, $failingResult->getException());
            $this->assertSame("Test had unexpected output:\n\n\"testProducesOutput\"", $failingResult->getException()->getMessage());
        });
    }

    public function testRandomizerIsUtilized() {
        Loop::run(function() {
            $dir = $this->implicitDefaultTestSuitePath('MultipleTest');
            $results = yield $this->parser->parse($dir);
            $testSuites = $results->getTestSuiteModels();
            $randomizer = $this->getMockBuilder(Randomizer::class)->getMock();
            $mockBridgeFactory = $this->createMock(MockBridgeFactory::class);

            $testSuiteRunner = new TestSuiteRunner(
                $this->emitter,
                $this->customAssertionContext,
                $randomizer,
                $mockBridgeFactory
            );

            $this->assertCount(1, $testSuites);
            $this->assertNotEmpty($testSuites[0]->getTestCaseModels());
            $randomizer->expects($this->exactly(3))
                ->method('randomize')
                ->withConsecutive(
                    [$testSuites],
                    [$testSuites[0]->getTestCaseModels()],
                    [$testSuites[0]->getTestCaseModels()[0]->getTestModels()]
                )
                ->willReturnOnConsecutiveCalls(
                    $testSuites,
                    $testSuites[0]->getTestCaseModels(),
                    $testSuites[0]->getTestCaseModels()[0]->getTestModels()
                );

            yield $testSuiteRunner->runTestSuites($results);
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionOnly() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionOnly'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(TestState::Passed, $this->actual[0]->getState());
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionWrongType() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionWrongType'));

            $this->assertCount(1, $this->actual);
            $this->assertInstanceOf(TestFailedException::class, $this->actual[0]->getException());
            $expected = sprintf(
                'Failed asserting that thrown exception %s extends expected %s',
                InvalidStateException::class,
                InvalidArgumentException::class
            );
            $this->assertSame($expected, $this->actual[0]->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionMessage() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionMessage'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(TestState::Passed, $this->actual[0]->getState());
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionWrongMessage() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionWrongMessage'));

            $this->assertCount(1, $this->actual);
            $this->assertInstanceOf(TestFailedException::class, $this->actual[0]->getException());
            $expected = sprintf(
                'Failed asserting that thrown exception message "%s" equals expected "%s"',
                'This is NOT the message that I expect',
                'This is the message that I expect'
            );
            $this->assertSame($expected, $this->actual[0]->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionDoesNotThrow() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionDoesNotThrow'));

            $this->assertCount(1, $this->actual);
            $this->assertInstanceOf(TestFailedException::class, $this->actual[0]->getException());
            $expected = sprintf(
                'Failed asserting that an exception of type %s is thrown',
                InvalidArgumentException::class
            );
            $this->assertSame($expected, $this->actual[0]->getException()->getMessage());
        });
    }

    public function testTestProcessingEventsEmittedInOrder() {
        Loop::run(function() {
            $actual = [];
            $this->emitter->on(Events::TEST_PROCESSED, function() use(&$actual) {
                $actual[] = 'test invoked';
            });
            $this->emitter->on(Events::PROCESSING_FINISHED, function() use(&$actual) {
                $actual[] = 'test processing finished';
            });
            $this->emitter->on(Events::PROCESSING_STARTED, function() use(&$actual) {
                $actual[] = 'test processing started';
            });

            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

            $this->assertSame(['test processing started', 'test invoked', 'test processing finished'], $actual);
        });
    }

    public function testImplicitDefaultTestSuiteTestExpectsNoAssertionsHasPassedState() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsNoAssertions'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(TestState::Passed, $this->actual[0]->getState());
        });
    }

    public function testImplicitDefaultTestSuiteExpectsNoAssertionsAssertMade() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsNoAssertionsAssertMade'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(TestState::Failed, $this->actual[0]->getState());
            $this->assertSame('Expected ' . ImplicitDefaultTestSuite\TestExpectsNoAssertionsAssertMade\MyTestCase::class .  '::testNoAssertionAssertionMade to make 0 assertions but made 2', $this->actual[0]->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteExpectsNoAssertionsAsyncAssertMade() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsNoAsyncAssertionsAssertMade'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(TestState::Failed, $this->actual[0]->getState());
            $this->assertSame('Expected ' . ImplicitDefaultTestSuite\TestExpectsNoAsyncAssertionsAssertMade\MyTestCase::class .  '::noAssertionButAsyncAssertionMade to make 0 assertions but made 2', $this->actual[0]->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestHasTimeoutExceedsValueIsFailedTest() : void {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestHasTimeout'));

            $this->assertCount(1, $this->actual);
            $this->assertSame(TestState::Failed, $this->actual[0]->getState());
            $msg = sprintf(
                'Expected %s::timeOutTest to complete within 100ms',
                ImplicitDefaultTestSuite\TestHasTimeout\MyTestCase::class
            );
            $this->assertSame($msg, $this->actual[0]->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteSingleMockTestWithBridgeSet() : void {
        Loop::run(function() {
            $this->testSuiteRunner->setMockBridgeClass(MockBridgeStub::class);
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleMockTest'));

            $this->assertCount(1, $this->actual);
            $testResult = $this->actual[0];
            /** @var ImplicitDefaultTestSuite\SingleMockTest\MyTestCase $testCase */
            $testCase = $testResult->getTestCase();

            $this->assertEquals(TestState::Passed, $testResult->getState());
            $this->assertNotNull($testCase->getCreatedMock());
            $createdMock = $testCase->getCreatedMock()->class;

            $this->assertSame(Application::class, $createdMock);
        });
    }

    public function testImplicitDefaultTestSuiteSingleMockTestWithBridgeSetInitializeAndFinalizeCalled() : void {
        Loop::run(function() {
            $this->testSuiteRunner->setMockBridgeClass(MockBridgeStub::class);
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleMockTest'));

            $this->assertCount(1, $this->actual);

            $mockBridge = $this->actual[0]->getTestCase()->mocks();
            $expected = ['initialize', 'createMock ' . Application::class, 'finalize'];
            $actual = $mockBridge->getCalls();

            $this->assertSame($expected, $actual);
        });
    }

    public function testImplicitDefaultTestSuiteSingleMockTestWithFailingBridgeHasFailedTest() : void {
        Loop::run(function() {
            $this->buildTestSuiteRunner();
            $this->emitter->on(Events::TEST_PROCESSED, function(TestProcessedEvent $event) {
                $this->actual[] = $event->getTarget();
            });
            $this->testSuiteRunner->setMockBridgeClass(FailingMockBridgeStub::class);
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleMockTest'));

            $this->assertCount(1, $this->actual);
            $testResult = $this->actual[0];

            $this->assertEquals(TestState::Failed, $testResult->getState());
            $this->assertInstanceOf(MockFailureException::class, $testResult->getException());
            $this->assertSame('Thrown from the FailingMockBridgeStub', $testResult->getException()->getMessage());
        });
    }

    public function testImplicitDefaultTestSuiteTestCasePriorityEachHooks() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseHooksPriority'));

            $this->assertCount(1, $this->actual);
            $testResult = $this->actual[0];

            $this->assertEquals(TestState::Passed, $testResult->getState());

            $expected = [
                'beforeEachOne',
                'beforeEachTwo',
                'beforeEachThree',
                'afterEachOne',
                'afterEachTwo',
                'afterEachThree',
            ];
            $this->assertEquals($expected, $testResult->getTestCase()->getInvokedEach());
        });
    }

    public function testImplicitDefaultTestSuiteTestCasePriorityAllHooks() {
        Loop::run(function() {
            yield $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseHooksPriority'));

            $this->assertCount(1, $this->actual);
            $testResult = $this->actual[0];

            $this->assertEquals(TestState::Passed, $testResult->getState());

            $expected = [
                'beforeAllOne',
                'beforeAllTwo',
                'beforeAllThree',
                'afterAllOne',
                'afterAllTwo',
                'afterAllThree',
            ];
            $this->assertEquals($expected, $testResult->getTestCase()->getInvokedAll());
        });
    }

    public function testExplicitTestSuiteTestSuiteHookPriority() {
        Loop::run(function() {
            yield $this->parseAndRun($this->explicitTestSuitePath('TestSuiteHookPriority'));

            $this->assertCount(1, $this->actual);
            $testResult = $this->actual[0];

            $this->assertEquals(TestState::Passed, $testResult->getState());

            $expected = [
                'beforeAllOne',
                'beforeAllTwo',
                'beforeAllThree',
                'beforeEachOne',
                'beforeEachTwo',
                'beforeEachThree',
                'beforeEachTestOne',
                'beforeEachTestTwo',
                'beforeEachTestThree',
                'afterEachTestOne',
                'afterEachTestTwo',
                'afterEachTestThree',
                'afterEachOne',
                'afterEachTwo',
                'afterEachThree',
                'afterAllOne',
                'afterAllTwo',
                'afterAllThree',
            ];
            $this->assertEquals($expected, $testResult->getTestCase()->testSuite()->getInvokedHooks());
        });
    }

    private function fetchTestResultForTest(string $testClass, string $method) : TestResult {
        foreach ($this->actual as $testResult) {
            if ($testResult->getTestCase()::class === $testClass && $testResult->getTestMethod() === $method) {
                return $testResult;
            }
        }
        $this->fail('Expected $this->actual to have a TestCase and method matching ' . $testClass . '::' . $method);
    }
}