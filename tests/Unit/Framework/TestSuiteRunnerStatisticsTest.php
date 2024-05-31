<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Acme\DemoSuites\ExplicitTestSuite;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Amp\Future;
use Labrador\AsyncEvent\AbstractListener;
use Labrador\AsyncEvent\Event;
use Labrador\AsyncEvent\Listener;
use Labrador\AsyncUnit\Framework\Event\Events;
use Labrador\AsyncUnit\Framework\Event\ProcessingFinishedEvent;
use Labrador\AsyncUnit\Framework\Event\ProcessingStartedEvent;
use Labrador\AsyncUnit\Framework\Event\TestProcessedEvent;
use Labrador\AsyncUnit\Framework\Event\TestSuiteFinishedEvent;
use Labrador\AsyncUnit\Framework\ImplicitTestSuite;
use Labrador\AsyncUnit\Framework\MockBridge\MockeryMockBridge;
use Labrador\AsyncUnit\Framework\Statistics\AggregateSummary;
use Labrador\CompositeFuture\CompositeFuture;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestSuiteRunnerStatisticsTest extends PHPUnitTestCase {

    use UsesAcmeSrc;
    use TestSuiteRunnerScaffolding;

    public function setUp(): void {
        $this->buildTestSuiteRunner();
    }

    private function createEventRecordingListener() : Listener {
        return new class() implements Listener {

            public array $actual = [];

            public function handle(Event $event) : Future|CompositeFuture|null {
                $this->actual[] = $event;
                return null;
            }
        };
    }

    public function testTestProcessingStartedHasAggregateSummary() {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('TestCaseDisabled'));
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_STARTED, $listener);

        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingStartedEvent $testStartedEvent */
        $testStartedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingStartedEvent::class, $testStartedEvent);
        $this->assertInstanceOf(AggregateSummary::class, $testStartedEvent->payload());
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);

        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);

        $summary = $testFinishedEvent->payload();

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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);

        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getTotalTestSuiteCount());
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getDisabledTestSuiteCount());
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getTotalTestCaseCount());
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getDisabledTestCaseCount());
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getTotalTestCount());
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getDisabledTestCount());
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getPassedTestCount());
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getFailedTestCount());
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getErroredTestCount());
    }

    public function processedAggregateSummaryWithCorrectAssertionCountProvider() : array {
        return [
            'SingleTest' => [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            'MultipleTest' => [$this->implicitDefaultTestSuitePath('MultipleTest'), 3],
            'KitchenSink' => [$this->implicitDefaultTestSuitePath('KitchenSink'), 10],
            'ExtendedTestCases' => [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), 22]
        ];
    }

    /**
     * @dataProvider processedAggregateSummaryWithCorrectAssertionCountProvider
     */
    public function testProcessedAggregateSummaryWithCorrectAssertionCount(string $path, int $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertCount(1, $listener->actual);
        /** @var ProcessingFinishedEvent $testFinishedEvent */
        $testFinishedEvent = $listener->actual[0];

        $this->assertInstanceOf(ProcessingFinishedEvent::class, $testFinishedEvent);
        $this->assertSame($expected, $testFinishedEvent->payload()->getAssertionCount());
    }

    public function processedTestSuiteSummaryTestSuiteNameProvider() : array {
        return [
            'SingleTest' => [$this->implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class]],
            'KitchenSink' => [$this->implicitDefaultTestSuitePath('KitchenSink'), [
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $this->assertEqualsCanonicalizing(
            $expected,
            array_map(static fn(Event $event) => $event->payload()->getTestSuiteName(), $listener->actual)
        );
    }

    public function processedTestSuiteSummaryTestCaseNamesProvider() : array {
        return [
            'SingleTest' => [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => [ImplicitDefaultTestSuite\SingleTest\MyTestCase::class]
            ]],
            'KitchenSink' => [$this->implicitDefaultTestSuitePath('KitchenSink'), [
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
            'TestSuiteDisabled' => [$this->explicitTestSuitePath('TestSuiteDisabled'), [
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
            $listener = $this->createEventRecordingListener();
            $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
            $this->testSuiteRunner->runTestSuites($results);

            $actual = [];
            foreach ($listener->actual as $event) {
                $actual[$event->payload()->getTestSuiteName()] = $event->payload()->getTestCaseNames();
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
            'SingleTest' => [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 1,
            ]],
            'ExtendedTestCases' => [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [
                ImplicitTestSuite::class => 3
            ]],
            'TestCaseDefinesTestSuite' => [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            'TestSuiteDisabled' => [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 2
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryTotalTestCaseCountProvider
     */
    public function testProcessedTestSuiteSummaryHasTotalTestCaseCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestSuiteName()] = $event->payload()->getTestCaseCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryDisabledTestCaseCountProvider() : array {
        return [
            'SingleTest' => [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 0,
            ]],
            'TestCaseDisabled' => [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            'TestCaseDefinesTestSuite' => [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            'TestSuiteDisabled' => [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 2
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryDisabledTestCaseCountProvider
     */
    public function testProcessedTestSuiteSummaryHasDisabledTestCaseCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestSuiteName()] = $event->payload()->getDisabledTestCaseCount();
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestSuiteName()] = $event->payload()->getTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryDisabledTestCountProvider() : array {
        return [
            'SingleTest' => [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 0,
            ]],
            'TestCaseDisabled' => [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 3
            ]],
            'TestCaseDefinesTestSuite' => [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            'TestSuiteDisabled' => [$this->explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 3
            ]],
            'TestDisabled' => [$this->implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            'ExceptionThrowingTest' => [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitTestSuite::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryDisabledTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasDisabledTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestSuiteName()] = $event->payload()->getDisabledTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryPassedTestCountProvider() : array {
        return [
            'SingleTest' => [$this->implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class => 1,]],
            'TestCaseDisabled' => [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            'TestCaseDefinesTestSuite' => [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            'ExtendedTestCases' => [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 8]],
            'TestDisabled' => [$this->implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            'ExceptionThrowingTest' => [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitTestSuite::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryPassedTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasPassedTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestSuiteName()] = $event->payload()->getPassedTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryFailedTestCountProvider() : array {
        return [
            'FailedAssertion' => [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 1,]],
            'TestCaseDisabled' => [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            'TestCaseDefinesTestSuite' => [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            'ExtendedTestCases' => [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 1]],
            'FailedNotAssertion' => [$this->implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 1]],
            'ExceptionThrowingTest' => [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [ImplicitTestSuite::class => 0]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryFailedTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasFailedTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestSuiteName()] = $event->payload()->getFailedTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryErroredTestCountProvider() : array {
        return [
            'FailedAssertion' => [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 0]],
            'TestCaseDisabled' => [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            'TestCaseDefinesTestSuite' => [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            'ExtendedTestCases' => [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 0]],
            'FailedNotAssertion' => [$this->implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 0]],
            'ExceptionThrowingTest' => [$this->implicitDefaultTestSuitePath('ExceptionThrowingTest'), [ImplicitTestSuite::class => 1]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryErroredTestCountProvider
     */
    public function testProcessedTestSuiteSummaryHasErroredTestCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestSuiteName()] = $event->payload()->getErroredTestCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestSuiteSummaryAssertionCountProvider() : array {
        return [
            'FailedAssertion' => [$this->implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 1,]],
            'TestCaseDisabled' => [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            'TestCaseDefinesTestSuite' => [$this->explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            'ExtendedTestCases' => [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 22]],
            'FailedNotAssertion' => [$this->implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 1]]
        ];
    }

    /**
     * @dataProvider processedTestSuiteSummaryAssertionCountProvider
     */
    public function testProcessedTestSuiteSummaryHasAssertionCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestSuiteName()] = $event->payload()->getAssertionCount();
        }

        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryTestSuiteNameProvider() : array {
        return [
            'SingleTest' => [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => ImplicitTestSuite::class
            ]],
            'KitchenSink' => [$this->implicitDefaultTestSuitePath('KitchenSink'), [
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_CASE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];

        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestCaseName()] = $event->payload()->getTestSuiteName();
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_CASE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestCaseName()] = $event->payload()->getTestNames();
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_CASE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestCaseName()] = $event->payload()->getTestCount();
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_CASE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestCaseName()] = $event->payload()->getDisabledTestCount();
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_CASE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestCaseName()] = $event->payload()->getPassedTestCount();
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_CASE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestCaseName()] = $event->payload()->getFailedTestCount();
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_CASE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestCaseName()] = $event->payload()->getErroredTestCount();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function processedTestCaseSummaryAssertionCountProvider() : array {
        return [
            'SingleTest' => [$this->implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            'FailedAssertion' => [$this->implicitDefaultTestSuitePath('FailedAssertion'), [
                ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::class => 1,
            ]],
            'KitchenSink' => [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 2,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 2,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 4,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 0
            ]]
        ];
    }

    /**
     * @dataProvider processedTestCaseSummaryAssertionCountProvider
     */
    public function testProcessedTestCaseSummaryHasCorrectAssertionCount(string $path, array $expected) : void {
        $results = $this->parser->parse($path);
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_CASE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestCaseName()] = $event->payload()->getAssertionCount();
        }

        ksort($expected);
        ksort($actual);
        $this->assertEquals($expected, $actual);
    }

    public function testProcessedAggregateSummaryHasDuration() {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        self::assertCount(1, $listener->actual);
        $event = $listener->actual[0];
        $this->assertInstanceOf(ProcessingFinishedEvent::class, $event);
        $this->assertGreaterThan(600, $event->payload()->getDuration()->asMilliseconds());
    }

    public function testTestSuiteSummaryHasDuration() : void {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_SUITE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        self::assertCount(1, $listener->actual);
        $event = $listener->actual[0];
        $this->assertInstanceOf(TestSuiteFinishedEvent::class, $event);
        $this->assertGreaterThan(600, $event->payload()->getDuration()->asMilliseconds());
    }

    public function testTestCaseSummaryHasDuration() : void {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_CASE_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $expected = [
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\FirstTestCase::class => 99,
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\SecondTestCase::class => 199,
            ImplicitDefaultTestSuite\MultipleTestsKnownDuration\ThirdTestCase::class => 299
        ];

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[$event->payload()->getTestCaseName()] = $event->payload()->getDuration()->asMilliseconds();
        }

        foreach ($expected as $testCase => $duration) {
            $this->assertGreaterThanOrEqual($duration, $actual[$testCase]);
        }
    }

    public function testTestResultHasDuration() : void {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MultipleTestsKnownDuration'));
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_PROCESSED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $key = $event->payload()->getTestCase()::class . '::' . $event->payload()->getTestMethod();
            $actual[$key] = $event->payload()->getDuration()->asMilliseconds();
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
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_DISABLED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        $actual = [];
        foreach ($listener->actual as $event) {
            $actual[] = $event->payload()->getDuration()->asMilliseconds();
        }

        $this->assertCount(1, $actual);
        $this->assertSame(0.0, $actual[0]);
    }

    public function testProcessedAggregateSummaryHasMemoryUsageInBytes() {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('SingleTest'));
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::PROCESSING_FINISHED, $listener);
        $this->testSuiteRunner->runTestSuites($results);

        self::assertCount(1, $listener->actual);
        $event = $listener->actual[0];
        $this->assertInstanceOf(ProcessingFinishedEvent::class, $event);
        $this->assertGreaterThan(1000, $event->payload()->getMemoryUsageInBytes());
    }

    public function testTestCaseSummaryMockBridgeAssertionCount() {
        $results = $this->parser->parse($this->implicitDefaultTestSuitePath('MockeryTestNoAssertion'));
        $listener = $this->createEventRecordingListener();
        $this->emitter->register(Events::TEST_PROCESSED, $listener);
        $this->testSuiteRunner->setMockBridgeClass(MockeryMockBridge::class);
        $this->testSuiteRunner->runTestSuites($results);

        self::assertCount(1, $listener->actual);
        $event = $listener->actual[0];
        $this->assertInstanceOf(TestProcessedEvent::class, $event);
        $this->assertEquals(1, $event->payload()->getTestCase()->getAssertionCount());
    }
}
