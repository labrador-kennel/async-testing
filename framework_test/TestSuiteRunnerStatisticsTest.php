<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Event\ProcessingFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\ProcessingStartedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestCaseFinishedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestDisabledEvent;
use Cspray\Labrador\AsyncUnit\Event\TestProcessedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestSuiteFinishedEvent;
use Cspray\Labrador\AsyncUnit\MockBridge\MockeryMockBridge;
use Cspray\Labrador\AsyncUnit\Statistics\AggregateSummary;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use Labrador\AsyncEvent\AbstractListener;
use Labrador\AsyncEvent\Event;
use Labrador\AsyncEvent\Listener;
use Labrador\CompositeFuture\CompositeFuture;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use stdClass;

class TestSuiteRunnerStatisticsTest extends PHPUnitTestCase {

    use UsesAcmeSrc;
    use TestSuiteRunnerScaffolding;

    public function setUp(): void {
        $this->buildTestSuiteRunner();
    }

    private function createEventRecordingListener(string $event) : Listener {
        return new class($event) extends AbstractListener {

            public array $actual = [];

            public function __construct(
                private readonly string $event
            ) {}

            public function canHandle(string $eventName) : bool {
                return $this->event === $eventName;
            }

            public function handle(Event $event) : Future|CompositeFuture|null {
                $this->actual[] = $event;
                return null;
            }
        };
    }

    public function testTestProcessingStartedHasAggregateSummary() {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('TestCaseDisabled'));
        $listener = $this->createEventRecordingListener(Events::PROCESSING_STARTED);
        $this->emitter->register($listener);

        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingStartedEvent $testStartedEvent */
        $testStartedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingStartedEvent::class, $testStartedEvent);
        $this->assertInstanceOf(AggregateSummary::class, $testStartedEvent->getTarget());
    }

    public function processedAggregateSummaryTestSuiteInfoProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitTestSuite::class, ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class
            ]]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryTestSuiteInfoProvider
     */
    public function testTestProcessingFinishedHasProcessedAggregateSummaryWithCorrectTestSuiteNames(string $path, array $expected) {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);

        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);

        $summary = $testFinishedEvent->getTarget();

        $this->assertEqualsCanonicalizing(
            $expected,
            $summary->getTestSuiteNames()
        );
    }

    public function processedAggregateSummaryWithCorrectTotalTestSuiteCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 3]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectTotalTestSuiteCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectTotalTestSuiteCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);

        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getTotalTestSuiteCount());
    }


    public function processedAggregateSummaryWithCorrectDisabledTestSuiteCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 0],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 1]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectDisabledTestSuiteCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectDisabledTestSuiteCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getDisabledTestSuiteCount());
    }

    public function processedAggregateSummaryWithCorrectTotalTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 7],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 2]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectTotalTestCaseCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectTotalTestCaseCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getTotalTestCaseCount());
    }

    public function processedAggregateSummaryWithCorrectDisabledTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 0],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 2],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 1]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectDisabledTestCaseCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectDisabledTestCaseCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getDisabledTestCaseCount());
    }

    public function processedAggregateSummaryWithCorrectTotalTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 13],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 3],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 3],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), 1]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectTotalTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectTotalTestCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getTotalTestCount());
    }

    public function processedAggregateSummaryWithCorrectDisabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 3],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 3],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 3],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), 0]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectDisabledTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectDisabledTestCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getDisabledTestCount());
    }

    public function processedAggregateSummaryWithCorrectPassedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 8],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), 0]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectPassedTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectPassedTestCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getPassedTestCount());
    }

    public function processedAggregateSummaryWithCorrectFailedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 1],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), 1],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), 0]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectFailedTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectFailedTestCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getFailedTestCount());
    }

    public function processedAggregateSummaryWithCorrectErroredTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 1],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 0],
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), 0],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), 1]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectErroredTestCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectErroredTestCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getErroredTestCount());
    }

    public function processedAggregateSummaryWithCorrectAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('MultipleTest'), 3],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 4],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), 18]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectAssertionCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectAssertionCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getAssertionCount());
    }

    public function processedAggregateSummaryWithCorrectAsyncAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('MultipleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 6],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), 4]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectAsyncAssertionCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectAsyncAssertionCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->getTarget()->getAsyncAssertionCount());
    }

    public function processedTestSuiteSummaryTestSuiteNameProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryTestSuiteNameProvider
     */
    public function testProcessedTestSuiteSummaryHasCorrectTestSuiteName(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertEqualsCanonicalizing(
            $expected,
            array_map(static fn(Event $event) => $event->getTarget()->getTestSuiteName(), $listener->actual)
        );
    }

    public function processedTestSuiteSummaryTestCaseNamesProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => [ImplicitDefaultTestSuite\SingleTest\MyTestCase::class]
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class => [
                    ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class,
                    ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class,
                ],
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class => [
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class,
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class,
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class,
                ],
                ImplicitTestSuite::class => [
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class,
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class
                ]
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => [
                    ExplicitTestSuite\TestSuiteDisabled\FirstTestCase::class,
                    ExplicitTestSuite\TestSuiteDisabled\SecondTestCase::class
                ]
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryTestCaseNamesProvider
     */
    public function testProcessedTestSuiteSummaryHasTestCaseNames(string $path, array $expected) : void {
            $results = $this->parser->parse($path);
            $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
            $this->emitter->register($listener);
            $this->testSuiteRunner->runTestSuites($results);

            $actual = [];
            foreach ($listener->actual as $event) {
                $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getTestCaseNames();
            }

            $testSuites = array_keys($actual);

            $this->assertNotEmpty($testSuites);

            foreach ($testSuites as $testSuite) {
                $this->assertArrayHasKey($testSuite, $expected);

                $this->assertEqualsCanonicalizing($expected[$testSuite], $actual[$testSuite]);
            }
    }

    public function processedTestSuiteSummaryTotalTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [
                ImplicitTestSuite::class => 3
            ]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 2
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryTotalTestCaseCountProvider
     */
    public function testProcessedTestSuiteSummaryHasTotalTestCaseCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getTestCaseCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryDisabledTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 0,
            ]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 2
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryDisabledTestCaseCountProvider
     */
    public function testProcessedTestSuiteSummaryHasDisabledTestCaseCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getDisabledTestCaseCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryTotalTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 3
            ]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 3
            ]],
            [$this->implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 2
            ]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitTestSuite::class => 1
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryTotalTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasTotalTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryDisabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 0,
            ]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 3
            ]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 3
            ]],
            [$this->implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitTestSuite::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryDisabledTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasDisabledTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getDisabledTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryPassedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class => 1,]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 8]],
            [$this->implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitTestSuite::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryPassedTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasPassedTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getPassedTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryFailedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 1,]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 1]],
            [$this->implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 1]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [ImplicitTestSuite::class => 0]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryFailedTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasFailedTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getFailedTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryErroredTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 0]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 0]],
            [$this->implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 0]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [ImplicitTestSuite::class => 1]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryErroredTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasErroredTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getErroredTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 1,]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 18]],
            [$this->implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 1]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryAssertionCountProvider
     */
    public function testProcessedTestSuiteSummaryHasAssertionCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getAssertionCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryAsyncAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 0,]],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 4]],
            [$this->implicitDefaultTestSuitePath('SingleTestAsyncAssertion'), [ImplicitTestSuite::class => 1]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryAsyncAssertionCountProvider
     */
    public function testProcessedTestSuiteSummaryHasAsyncAssertionCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestSuiteName()] = $event->getTarget()->getAsyncAssertionCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryTestSuiteNameProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => ImplicitTestSuite::class
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => ImplicitTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => ImplicitTestSuite::class
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryTestSuiteNameProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectTestSuiteName(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];

        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getTestSuiteName();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryTestNamesProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => [
                    ImplicitDefaultTestSuite\SingleTest\MyTestCase::class . '::ensureSomethingHappens'
                ]
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::testOne',
                    ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::testTwo',
                    ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::disabledTest'
                ],
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class . '::checkTwo',
                    ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class . '::checkTwoDisabled'
                ],
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class . '::isBestHobbit',
                ],
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class . '::isBestHobbit'
                ],
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class . '::isBestHobbit'
                ],
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class . '::checkFood#0',
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class . '::checkFood#1',
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class . '::checkFood#2',
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class . '::checkFood#3'
                ],
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => [
                    ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class . '::throwException'
                ]
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryTestNamesProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectTestNames(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getTestNames();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 3,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 2,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 4,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 1
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryTestCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getTestCount();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryDisabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryDisabledTestCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectDisabledTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getDisabledTestCount();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryPassedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 2,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 4,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryPassedTestCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectPassedTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getPassedTestCount();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryFailedTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [
                ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryFailedTestCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectFailedTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getFailedTestCount();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryErroredTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [
                ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::class => 0,
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 1
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryErroredTestCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectErroredTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getErroredTestCount();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            [$this->implicitDefaultTestSuitePath('FailedAssertion'), [
                ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class =>1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryAssertionCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectAssertionCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getAssertionCount();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryAsyncAssertionCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [$this->implicitDefaultTestSuitePath('SingleTestAsyncAssertion'), [
                ImplicitDefaultTestSuite\SingleTestAsyncAssertion\MyTestCase::class => 1,
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class =>1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 4,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryAsyncAssertionCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectAsyncAssertionCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getAsyncAssertionCount();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testProcessedAggregateSummaryHasDuration() {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        self::assertCount(1, $listener->actual);
        $event = $listener->actual[0];
        $this->assertInstanceOf(ProcessingFinishedEvent::class, $event);
        $this->assertGreaterThan(600, $event->getTarget()->getDuration()->asMilliseconds());
    }

    public function testTestSuiteSummaryHasDuration() : void {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
        $listener = $this->createEventRecordingListener(Events::TEST_SUITE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        self::assertCount(1, $listener->actual);
        $event = $listener->actual[0];
        $this->assertInstanceOf(TestSuiteFinishedEvent::class, $event);
        $this->assertGreaterThan(600, $event->getTarget()->getDuration()->asMilliseconds());
    }

    public function testTestCaseSummaryHasDuration() : void {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
        $listener = $this->createEventRecordingListener(Events::TEST_CASE_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $expected = [
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\FirstTestCase::class => 99,
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\SecondTestCase::class => 199,
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\ThirdTestCase::class => 299
        ];

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->getTarget()->getTestCaseName()] = $event->getTarget()->getDuration()->asMilliseconds();
        }

        foreach ($expected as $testCase => $duration) {
            $this->assertGreaterThanOrEqual($duration, $actual[$testCase]);
        }
    }

    public function testTestResultHasDuration() : void {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
        $listener = $this->createEventRecordingListener(Events::TEST_PROCESSED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $key = $event->getTarget()->getTestCase()::class . '::' . $event->getTarget()->getTestMethod();
            $actual[$key] = $event->getTarget()->getDuration()->asMilliseconds();
        }

        $expected = [
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\FirstTestCase::class . '::checkOne' => 99,
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\SecondTestCase::class . '::checkOne' => 99,
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\SecondTestCase::class . '::checkTwo' => 99,
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\ThirdTestCase::class . '::checkOne' => 99,
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\ThirdTestCase::class . '::checkTwo' => 99,
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\ThirdTestCase::class . '::checkThree' => 99
        ];

        foreach ($expected as $testCase => $duration) {
            $this->assertGreaterThanOrEqual($duration, $actual[$testCase], $testCase . ' did not execute long enough');
        }
    }

    public function testDisabledTestHasZeroDuration() : void {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('TestDisabled'));
        $listener = $this->createEventRecordingListener(Events::TEST_DISABLED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[] = $event->getTarget()->getDuration()->asMilliseconds();
        }

        $this->assertCount(1, $actual);
        $this->assertSame(0.0, $actual[0]);
    }

    public function testProcessedAggregateSummaryHasMemoryUsageInBytes() {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('SingleTest'));
        $listener = $this->createEventRecordingListener(Events::PROCESSING_FINISHED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->runTestSuites($results);

        self::assertCount(1, $listener->actual);
        $event = $listener->actual[0];
        $this->assertInstanceOf(ProcessingFinishedEvent::class, $event);
        $this->assertGreaterThan(1000, $event->getTarget()->getMemoryUsageInBytes());
    }

    public function testTestCaseSummaryMockBridgeAssertionCount() {
        $this->markTestSkipped('Need to reimplement MockBridge.');
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MockeryTestNoAssertion'));
        $listener = $this->createEventRecordingListener(Events::TEST_PROCESSED);
        $this->emitter->register($listener);
        $this->testSuiteRunner->setMockBridgeClass(MockeryMockBridge::class);
        $this->testSuiteRunner->runTestSuites($results);

        self::assertCount(1, $listener->actual);
        $event = $listener->actual[0];
        $this->assertInstanceOf(TestProcessedEvent::class, $event);
        $this->assertEquals(1, $event->getTarget()->getTestCase()->getAssertionCount());
    }
}
