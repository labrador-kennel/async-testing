<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Acme\DemoSuites\ExplicitTestSuite;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Amp\Future;
use Cspray\Labrador\AsyncUnit\Configuration\Configuration;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use Cspray\Labrador\AsyncUnit\Exception\MockFailureException;
use Cspray\Labrador\AsyncUnit\Exception\TestDisabledException;
use Cspray\Labrador\AsyncUnit\Exception\TestFailedException;
use Cspray\Labrador\AsyncUnit\Exception\TestOutputException;
use Cspray\Labrador\AsyncUnit\Stub\FailingMockBridgeStub;
use Cspray\Labrador\AsyncUnit\Stub\MockBridgeStub;
use Exception;
use Labrador\AsyncEvent\AbstractListener;
use Labrador\AsyncEvent\Event;
use Labrador\AsyncEvent\Listener;
use Labrador\CompositeFuture\CompositeFuture;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestSuiteRunnerTest extends PHPUnitTestCase {

    use UsesAcmeSrc;
    use TestSuiteRunnerScaffolding;


    private $listener;

    public function setUp() : void {
        $this->buildTestSuiteRunner();
        $this->listener = new class extends AbstractListener {

            private array $targets = [];

            public function canHandle(string $eventName) : bool {
                return $eventName === Events::TEST_PROCESSED;
            }

            public function handle(Event $event) : Future|CompositeFuture|null {
                $this->targets[] = $event->getTarget();
                return null;
            }

            public function getTargets() : array {
                return $this->targets;
            }
        };
        $this->emitter->register($this->listener);
    }

    public function tearDown(): void {
        ImplicitDefaultTestSuite\TestCaseHooksPriority\MyTestCase::clearInvokedAll();
    }

    private function parseAndRun(string $path) : void {
        $results = $this->parser->parse($path);
        $this->testSuiteRunner->runTestSuites($results);
    }

    private function createRecordingListener(array $events) : Listener {
        return new class($events) extends AbstractListener {
            public array $actual = [];

            public function __construct(
                private readonly array $events
            ) {}

            public function canHandle(string $eventName) : bool {
                return in_array($eventName, $this->events, true);
            }

            public function handle(Event $event) : Future|CompositeFuture|null {
                $this->actual[] = $event->getName();
                return null;
            }
        };
    }

    public function testImplicitDefaultTestSuiteSingleTestEmitsTestProcessedEventWithProperTestCaseInstance() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $this->listener->getTargets()[0]->getTestCase());
    }

    public function testImplicitDefaultTestSuiteSingleTestEmitsTestProcessedEventWithProperTestMethodName() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame('ensureSomethingHappens', $this->listener->getTargets()[0]->getTestMethod());
    }

    public function testImplicitDefaultTestSuiteSingleTestEmitsTestProcessedEventWithInvokedTestCase() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertTrue($this->listener->getTargets()[0]->getTestCase()->getTestInvoked());
    }

    public function testImplicitDefaultTestSuiteMultipleTestEmitsTestProcessedEventsEachTestUniqueTestCase() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('MultipleTest'));
        $this->assertCount(3, $this->listener->getTargets());

        $actual = [
            $this->listener->getTargets()[0]->getTestCase()->getInvokedMethods(),
            $this->listener->getTargets()[1]->getTestCase()->getInvokedMethods(),
            $this->listener->getTargets()[2]->getTestCase()->getInvokedMethods()
        ];
        $expected = [
            [ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappens'],
            [ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappensTwice'],
            [ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class . '::ensureSomethingHappensThreeTimes']
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testImplicitDefaultTestSuiteHasSingleBeforeAllHookInvokedBeforeTest() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('HasSingleBeforeAllHook'));

        $this->assertCount(2, $this->listener->getTargets());

        $actual = [
            $this->listener->getTargets()[0]->getTestCase()->getCombinedData(),
            $this->listener->getTargets()[1]->getTestCase()->getCombinedData()
        ];
        $expected = [
            ['beforeAll', 'ensureSomething'],
            ['beforeAll', 'ensureSomethingTwice']
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testImplicitDefaultTestSuiteHasSingleBeforeEachHookInvokedBeforeTest() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('HasSingleBeforeEachHook'));

        $this->assertCount(2, $this->listener->getTargets());
        $actual = [
            $this->listener->getTargets()[0]->getTestCase()->getData(),
            $this->listener->getTargets()[1]->getTestCase()->getData()
        ];
        $expected = [
            ['beforeEach', 'ensureSomething'],
            ['beforeEach', 'ensureSomethingTwice']
        ];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testImplicitDefaultTestSuiteHasSingleAfterAllHookInvokedAfterTest() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('HasSingleAfterAllHook'));

        $this->assertCount(2, $this->listener->getTargets());
        $actual = [
            $this->listener->getTargets()[0]->getTestCase()->getCombinedData(),
            $this->listener->getTargets()[1]->getTestCase()->getCombinedData(),
        ];
        // We expect the afterAll _first_ here because our test case combines the class data from AfterAll and the object
        // data from the TestCase with class data first.
        $expected = [
            ['afterAll', 'ensureSomething'],
            ['afterAll', 'ensureSomethingTwice']
        ];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testImplicitDefaultTestSuiteHasSingleAfterEachHookInvokedAfterTest() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('HasSingleAfterEachHook'));

        $this->assertCount(2, $this->listener->getTargets());
        $actual = [
            $this->listener->getTargets()[0]->getTestCase()->getData(),
            $this->listener->getTargets()[1]->getTestCase()->getData()
        ];
        $expected = [
            ['ensureSomething', 'afterEach'],
            ['ensureSomethingTwice', 'afterEach']
        ];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingTestEmitsTestProcessedEventWithErrorStateAndCorrectException() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('ExceptionThrowingTest'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(TestState::Errored, $this->listener->getTargets()[0]->getState());

        $this->assertNotNull($this->listener->getTargets()[0]->getException());
        $expectedMsg = 'An unexpected exception of type "Exception" with code 0 and message "Test failure" was thrown from #[Test] ' . ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class . '::throwsException';
        $this->assertSame($expectedMsg, $this->listener->getTargets()[0]->getException()->getMessage());
        $this->assertInstanceOf(Exception::class, $this->listener->getTargets()[0]->getException()->getPrevious());
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingTestWithAfterEachHookInvokedAfterTest() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('ExceptionThrowingTestWithAfterEachHook'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertTrue($this->listener->getTargets()[0]->getTestCase()->getAfterHookCalled());
    }

    public function testImplicitDefaultTestSuiteTestFailedExceptionThrowingTestEmitsTestProcessedEventDoesNotMarkExceptionAsUnexpected() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestFailedExceptionThrowingTest'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(TestState::Failed, $this->listener->getTargets()[0]->getState());

        $this->assertNotNull($this->listener->getTargets()[0]->getException());
        $this->assertSame('Something barfed', $this->listener->getTargets()[0]->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteCustomAssertionsEmitsTestProcessedEventWithCorrectData() {
        // Mock setup to make sure our custom assertion is being called properly
        $assertion = $this->getMockBuilder(Assertion::class)->getMock();
        $assertionResult = $this->getMockBuilder(AssertionResult::class)->getMock();
        $assertionResult->expects($this->once())->method('isSuccessful')->willReturn(true);
        $assertion->expects($this->once())->method('assert')->willReturn($assertionResult);

        $asyncAssertion = $this->getMockBuilder(AsyncAssertion::class)->getMock();
        $asyncAssertionResult = $this->getMockBuilder(AssertionResult::class)->getMock();
        $asyncAssertionResult->expects($this->once())->method('isSuccessful')->willReturn(true);
        $asyncAssertion->expects($this->once())->method('assert')->willReturn(Future::complete($asyncAssertionResult));

        $this->customAssertionContext->registerAssertion('theCustomAssertion', fn() => $assertion);
        $this->customAssertionContext->registerAsyncAssertion('theCustomAssertion', fn() => $asyncAssertion);

        // Normal TestSuiteRunner testing
        $this->parseAndRun($this->implicitDefaultTestSuitePath('CustomAssertions'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(TestState::Passed, $this->listener->getTargets()[0]->getState());
    }

    public function testImplicitDefaultTestSuiteHasDataProviderEmitsTestProcessedEventsForEachDataSetOnUniqueTestCase() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('HasDataProvider'));
        $this->assertCount(3, $this->listener->getTargets());

        $actual = [
            $this->listener->getTargets()[0]->getTestCase()->getCounter(),
            $this->listener->getTargets()[1]->getTestCase()->getCounter(),
            $this->listener->getTargets()[2]->getTestCase()->getCounter(),
        ];
        $expected = [1, 1, 1];

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testExplicitTestSuiteDefaultExplicitTestSuite() {
        $this->parseAndRun($this->explicitTestSuitePath('AnnotatedDefaultTestSuite'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(ExplicitTestSuite\AnnotatedDefaultTestSuite\MyTestSuite::class, $this->listener->getTargets()[0]->getTestCase()->testSuite()::class);
    }

    public function testImplicitDefaultTestSuiteMultipleBeforeAllHooksAllInvokedBeforeTest() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('MultipleBeforeAllHooks'));
        $this->assertCount(2, $this->listener->getTargets());
        $actual = [
            $this->listener->getTargets()[0]->getTestCase()->getState(),
            $this->listener->getTargets()[1]->getTestCase()->getState(),
        ];
        $expected = [
            ImplicitDefaultTestSuite\MultipleBeforeAllHooks\FirstTestCase::class,
            ImplicitDefaultTestSuite\MultipleBeforeAllHooks\SecondTestCase::class
        ];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testExplicitTestSuiteBeforeAllTestSuiteHookTestCaseHasAccessToSameTestSuite() : void {
        $this->parseAndRun($this->explicitTestSuitePath('BeforeAllTestSuiteHook'));
        $this->assertCount(3, $this->listener->getTargets());
        $actual = [
            $this->listener->getTargets()[0]->getTestCase()->testSuite(),
            $this->listener->getTargets()[1]->getTestCase()->testSuite(),
            $this->listener->getTargets()[2]->getTestCase()->testSuite(),
        ];
        $this->assertSame($actual[0], $actual[1]);
        $this->assertSame($actual[1], $actual[2]);
    }

    public function testTestPassedEventsEmittedAfterTestProcessedEvent() {
        $listener = $this->createRecordingListener([
            Events::TEST_PROCESSED,
            Events::TEST_PASSED
        ]);
        $this->emitter->register($listener);
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

        $this->assertSame([Events::TEST_PROCESSED, Events::TEST_PASSED], $listener->actual);
    }

    public function testTestFailedEventsEmittedAfterTestProcessedEvent() {
        $listener = $this->createRecordingListener([
            Events::TEST_PROCESSED,
            Events::TEST_FAILED
        ]);
        $this->emitter->register($listener);
        $this->parseAndRun($this->implicitDefaultTestSuitePath('FailedAssertion'));

        $this->assertSame([Events::TEST_PROCESSED, Events::TEST_FAILED], $listener->actual);
    }

    public function testTestErrorEventEmittedAfterTestProcessedEvent() {
        $listener = $this->createRecordingListener([
            Events::TEST_PROCESSED, Events::TEST_ERRORED
        ]);
        $this->emitter->register($listener);

        $this->parseAndRun($this->implicitDefaultTestSuitePath('ExceptionThrowingTest'));

        $this->assertSame([Events::TEST_PROCESSED, Events::TEST_ERRORED], $listener->actual);
    }

    public function testTestDisabledEventsEmittedAfterTestProcessedEvent() {
        $listener = $this->createRecordingListener([
            Events::TEST_PROCESSED, Events::TEST_DISABLED
        ]);
        $this->emitter->register($listener);
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTestDisabled'));

        $this->assertSame([Events::TEST_PROCESSED, Events::TEST_DISABLED], $listener->actual);
    }

    public function testTestSuiteStartedAndFinishedEventsEmittedInOrder() {
        $actual = [];
        $listener = $this->createRecordingListener([
            Events::TEST_SUITE_STARTED,
            Events::TEST_PROCESSED,
            Events::TEST_SUITE_FINISHED
        ]);
        $this->emitter->register($listener);
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

        $this->assertSame([Events::TEST_SUITE_STARTED, Events::TEST_PROCESSED, Events::TEST_SUITE_FINISHED], $listener->actual);
    }

    public function testTestCaseProcessingEventEmitted() {
        $listener = $this->createRecordingListener([
            Events::TEST_CASE_STARTED,
            Events::TEST_PROCESSED,
            Events::TEST_CASE_FINISHED
        ]);
        $this->emitter->register($listener);
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

        $this->assertSame([Events::TEST_CASE_STARTED, Events::TEST_PROCESSED, Events::TEST_CASE_FINISHED], $listener->actual);
    }

    public function testTestMethodIsNotInvokedWhenDisabled() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabled'));

        $this->assertCount(2, $this->listener->getTargets());
        $actual = [
            $this->listener->getTargets()[0]->getState(),
            $this->listener->getTargets()[1]->getState()
        ];
        $expected = [TestState::Passed, TestState::Disabled];
        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    public function testTestMethodIsNotInvokedWhenTestCaseDisabled() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseDisabled'));

        $this->assertCount(3, $this->listener->getTargets());
        $actualState = [
            $this->listener->getTargets()[0]->getState(),
            $this->listener->getTargets()[1]->getState(),
            $this->listener->getTargets()[2]->getState(),
        ];
        $expectedState = [TestState::Disabled, TestState::Disabled, TestState::Disabled];
        $this->assertEqualsCanonicalizing($expectedState, $actualState);

        $actualData = [
            $this->listener->getTargets()[0]->getTestCase()->getData(),
            $this->listener->getTargets()[1]->getTestCase()->getData(),
            $this->listener->getTargets()[2]->getTestCase()->getData(),
        ];
        $expectedData = [[], [], []];
        $this->assertEqualsCanonicalizing($expectedData, $actualData);
    }

    public function testTestResultWhenTestDisabled() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabled'));
        $disabledTestResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class, 'skippedTest');

        $this->assertSame(TestState::Disabled, $disabledTestResult->getState());
        $this->assertInstanceOf(TestDisabledException::class, $disabledTestResult->getException());
        $expected = sprintf(
            '%s::%s has been marked disabled via annotation',
            ImplicitDefaultTestSuite\TestDisabled\MyTestCase::class,
            'skippedTest'
        );
        $this->assertSame($expected, $disabledTestResult->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteHandleNonPhpFiles() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('HandleNonPhpFiles'));

        $this->assertCount(1, $this->listener->getTargets());
    }

    public function testImplicitDefaultTestSuiteTestDisabledHookNotInvoked() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabledHookNotInvoked'));

        $disabledTestResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledHookNotInvoked\MyTestCase::class, 'disabledTest');

        $this->assertSame(TestState::Disabled, $disabledTestResult->getState());
        $this->assertSame([], $disabledTestResult->getTestCase()->getState());

        $enabledTestResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledHookNotInvoked\MyTestCase::class, 'enabledTest');

        $this->assertSame(TestState::Passed, $enabledTestResult->getState());
        $this->assertSame(['before', 'enabled', 'after'], $enabledTestResult->getTestCase()->getState());
    }

    public function testImplicitDefaultTestSuiteTestCaseDisabledHookNotInvoked() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseDisabledHookNotInvoked'));

        $testOneResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestCaseDisabledHookNotInvoked\MyTestCase::class, 'testOne');

        $this->assertSame(TestState::Disabled, $testOneResult->getState());
        $this->assertSame([], $testOneResult->getTestCase()->getState());

        $testTwoResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestCaseDisabledHookNotInvoked\MyTestCase::class, 'testTwo');

        $this->assertSame(TestState::Disabled, $testTwoResult->getState());
        $this->assertSame([], $testTwoResult->getTestCase()->getState());
    }

    public function testExplicitTestSuiteTestSuiteDisabledHookNotInvoked() {
        $this->parseAndRun($this->explicitTestSuitePath('TestSuiteDisabledHookNotInvoked'));

        $testSomethingResult = $this->fetchTestResultForTest(ExplicitTestSuite\TestSuiteDisabledHookNotInvoked\MyTestCase::class, 'testSomething');

        $this->assertSame(TestState::Disabled, $testSomethingResult->getState());
        $this->assertSame([], $testSomethingResult->getTestCase()->testSuite()->getState());
    }

    public function testImplicitDefaultTestSuiteTestDisabledCustomMessage() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabledCustomMessage'));

        $testOneResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledCustomMessage\MyTestCase::class, 'testOne');

        $this->assertSame(TestState::Disabled, $testOneResult->getState());
        $this->assertInstanceOf(TestDisabledException::class, $testOneResult->getException());
        $this->assertSame('Not sure what we should do here yet', $testOneResult->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteTestCaseDisabledCustomMessage() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseDisabledCustomMessage'));

        $testOneResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestCaseDisabledCustomMessage\MyTestCase::class, 'testOne');

        $this->assertSame(TestState::Disabled, $testOneResult->getState());
        $this->assertInstanceOf(TestDisabledException::class, $testOneResult->getException());
        $this->assertSame('The TestCase is disabled', $testOneResult->getException()->getMessage());
    }

    public function testExplicitTestSuiteTestSuiteDisabledCustomMessage() {
        $this->parseAndRun($this->explicitTestSuitePath('TestSuiteDisabledCustomMessage'));

        $testOneResult = $this->fetchTestResultForTest(ExplicitTestSuite\TestSuiteDisabledCustomMessage\MyTestCase::class, 'testOne');

        $this->assertSame(TestState::Disabled, $testOneResult->getState());
        $this->assertInstanceOf(TestDisabledException::class, $testOneResult->getException());
        $this->assertSame('The AttachToTestSuite is disabled', $testOneResult->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteTestEventsHaveCorrectState() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestDisabledEvents'));

        $failingResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledEvents\MyTestCase::class, 'testFailingFloatEquals');

        $this->assertSame(TestState::Failed, $failingResult->getState());

        $passingResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledEvents\MyTestCase::class, 'testIsTrue');

        $this->assertSame(TestState::Passed, $passingResult->getState());

        $disabledResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestDisabledEvents\MyTestCase::class, 'testIsDisabled');

        $this->assertSame(TestState::Disabled, $disabledResult->getState());
    }

    public function testImplicitDefaultTestSuiteTestHasOutput() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestHasOutput'));

        $failingResult = $this->fetchTestResultForTest(ImplicitDefaultTestSuite\TestHasOutput\MyTestCase::class, 'testProducesOutput');

        $this->assertInstanceOf(TestOutputException::class, $failingResult->getException());
        $this->assertSame("Test had unexpected output:\n\n\"testProducesOutput\"", $failingResult->getException()->getMessage());
    }

    public function testRandomizerIsUtilized() {
        $dir = $this->implicitDefaultTestSuitePath('MultipleTest');
        $results = $this->parser->parse($dir);
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

        $testSuiteRunner->runTestSuites($results);
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionOnly() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionOnly'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(TestState::Passed, $this->listener->getTargets()[0]->getState());
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionWrongType() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionWrongType'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertInstanceOf(TestFailedException::class, $this->listener->getTargets()[0]->getException());
        $expected = sprintf(
            'Failed asserting that thrown exception %s extends expected %s',
            InvalidStateException::class,
            InvalidArgumentException::class
        );
        $this->assertSame($expected, $this->listener->getTargets()[0]->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionMessage() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionMessage'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(TestState::Passed, $this->listener->getTargets()[0]->getState());
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionWrongMessage() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionWrongMessage'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertInstanceOf(TestFailedException::class, $this->listener->getTargets()[0]->getException());
        $expected = sprintf(
            'Failed asserting that thrown exception message "%s" equals expected "%s"',
            'This is NOT the message that I expect',
            'This is the message that I expect'
        );
        $this->assertSame($expected, $this->listener->getTargets()[0]->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteTestExpectsExceptionDoesNotThrow() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsExceptionDoesNotThrow'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertInstanceOf(TestFailedException::class, $this->listener->getTargets()[0]->getException());
        $expected = sprintf(
            'Failed asserting that an exception of type %s is thrown',
            InvalidArgumentException::class
        );
        $this->assertSame($expected, $this->listener->getTargets()[0]->getException()->getMessage());
    }

    public function testTestProcessingEventsEmittedInOrder() {
        $listener = $this->createRecordingListener([
            Events::TEST_PROCESSED,
            Events::PROCESSING_FINISHED,
            Events::PROCESSING_STARTED
        ]);
        $this->emitter->register($listener);
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleTest'));

        $this->assertSame([Events::PROCESSING_STARTED, Events::TEST_PROCESSED, Events::PROCESSING_FINISHED], $listener->actual);
    }

    public function testImplicitDefaultTestSuiteTestExpectsNoAssertionsHasPassedState() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsNoAssertions'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(TestState::Passed, $this->listener->getTargets()[0]->getState());
    }

    public function testImplicitDefaultTestSuiteExpectsNoAssertionsAssertMade() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsNoAssertionsAssertMade'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(TestState::Failed, $this->listener->getTargets()[0]->getState());
        $this->assertSame('Expected ' . ImplicitDefaultTestSuite\TestExpectsNoAssertionsAssertMade\MyTestCase::class .  '::testNoAssertionAssertionMade to make 0 assertions but made 2', $this->listener->getTargets()[0]->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteExpectsNoAssertionsAsyncAssertMade() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestExpectsNoAsyncAssertionsAssertMade'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(TestState::Failed, $this->listener->getTargets()[0]->getState());
        $this->assertSame('Expected ' . ImplicitDefaultTestSuite\TestExpectsNoAsyncAssertionsAssertMade\MyTestCase::class .  '::noAssertionButAsyncAssertionMade to make 0 assertions but made 2', $this->listener->getTargets()[0]->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteTestHasTimeoutExceedsValueIsFailedTest() : void {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestHasTimeout'));

        $this->assertCount(1, $this->listener->getTargets());
        $this->assertSame(TestState::Failed, $this->listener->getTargets()[0]->getState());
        $msg = sprintf(
            'Expected %s::timeOutTest to complete within 100ms',
            ImplicitDefaultTestSuite\TestHasTimeout\MyTestCase::class
        );
        $this->assertSame($msg, $this->listener->getTargets()[0]->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteSingleMockTestWithBridgeSet() : void {
        $this->testSuiteRunner->setMockBridgeClass(MockBridgeStub::class);
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleMockTest'));

        $this->assertCount(1, $this->listener->getTargets());
        $testResult = $this->listener->getTargets()[0];
        /** @var ImplicitDefaultTestSuite\SingleMockTest\MyTestCase $testCase */
        $testCase = $testResult->getTestCase();

        $this->assertEquals(TestState::Passed, $testResult->getState());
        $this->assertNotNull($testCase->getCreatedMock());
        $createdMock = $testCase->getCreatedMock()->class;

        $this->assertSame(Configuration::class, $createdMock);
    }

    public function testImplicitDefaultTestSuiteSingleMockTestWithBridgeSetInitializeAndFinalizeCalled() : void {
        $this->testSuiteRunner->setMockBridgeClass(MockBridgeStub::class);
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleMockTest'));

        $this->assertCount(1, $this->listener->getTargets());

        $mockBridge = $this->listener->getTargets()[0]->getTestCase()->mocks();
        $expected = ['initialize', 'createMock ' . Configuration::class, 'finalize'];
        $actual = $mockBridge->getCalls();

        $this->assertSame($expected, $actual);
    }

    public function testImplicitDefaultTestSuiteSingleMockTestWithFailingBridgeHasFailedTest() : void {
        $this->buildTestSuiteRunner();
        $this->emitter->register($this->listener);
        $this->testSuiteRunner->setMockBridgeClass(FailingMockBridgeStub::class);
        $this->parseAndRun($this->implicitDefaultTestSuitePath('SingleMockTest'));

        $this->assertCount(1, $this->listener->getTargets());
        $testResult = $this->listener->getTargets()[0];

        $this->assertEquals(TestState::Failed, $testResult->getState());
        $this->assertInstanceOf(MockFailureException::class, $testResult->getException());
        $this->assertSame('Thrown from the FailingMockBridgeStub', $testResult->getException()->getMessage());
    }

    public function testImplicitDefaultTestSuiteTestCasePriorityEachHooks() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseHooksPriority'));

        $this->assertCount(1, $this->listener->getTargets());
        $testResult = $this->listener->getTargets()[0];

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
    }

    public function testImplicitDefaultTestSuiteTestCasePriorityAllHooks() {
        $this->parseAndRun($this->implicitDefaultTestSuitePath('TestCaseHooksPriority'));

        $this->assertCount(1, $this->listener->getTargets());
        $testResult = $this->listener->getTargets()[0];

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
    }

    public function testExplicitTestSuiteTestSuiteHookPriority() {
        $this->parseAndRun($this->explicitTestSuitePath('TestSuiteHookPriority'));

        $this->assertCount(1, $this->listener->getTargets());
        $testResult = $this->listener->getTargets()[0];

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
    }

    private function fetchTestResultForTest(string $testClass, string $method) : TestResult {
        foreach ($this->listener->getTargets() as $testResult) {
            if ($testResult->getTestCase()::class === $testClass && $testResult->getTestMethod() === $method) {
                return $testResult;
            }
        }
        $this->fail('Expected $this->listener->getTargets() to have a TestCase and method matching ' . $testClass . '::' . $method);
    }
}