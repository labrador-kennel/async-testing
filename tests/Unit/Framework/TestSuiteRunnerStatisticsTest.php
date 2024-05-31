<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Acme\DemoSuites\ExplicitTestSuite;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Amp\Future;
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
use PHPUnit\Framework\Attributes\DataProvider;
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

    public static function processedAggregateSummaryTestSuiteInfoProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class]],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitTestSuite::class, ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class
            ]]
        ];
    }

    #[DataProvider('processedAggregateSummaryTestSuiteInfoProvider')]
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

    public static function processedAggregateSummaryWithCorrectTotalTestSuiteCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 1],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 3]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectTotalTestSuiteCountProvider')]
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


    public static function processedAggregateSummaryWithCorrectDisabledTestSuiteCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 0],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 0],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 1]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectDisabledTestSuiteCountProvider')]
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

    public static function processedAggregateSummaryWithCorrectTotalTestCaseCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 1],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 7],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 2]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectTotalTestCaseCountProvider')]
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

    public static function processedAggregateSummaryWithCorrectDisabledTestCaseCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 0],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 0],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 2],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), 1]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectDisabledTestCaseCountProvider')]
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

    public static function processedAggregateSummaryWithCorrectTotalTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 1],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 13],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 3],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), 3],
            'ExceptionThrowingTest' => [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), 1]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectTotalTestCountProvider')]
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

    public static function processedAggregateSummaryWithCorrectDisabledTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 0],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 3],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 3],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), 3],
            'ExceptionThrowingTest' => [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), 0]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectDisabledTestCountProvider')]
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

    public static function processedAggregateSummaryWithCorrectPassedTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 1],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 8],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 0],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), 0],
            'ExceptionThrowingTest' => [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), 0]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectPassedTestCountProvider')]
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

    public static function processedAggregateSummaryWithCorrectFailedTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 0],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 1],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 0],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), 0],
            'FailedAssertion' => [self::implicitDefaultTestSuitePath('FailedAssertion'), 1],
            'ExceptionThrowingTest' => [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), 0]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectFailedTestCountProvider')]
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

    public static function processedAggregateSummaryWithCorrectErroredTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 0],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 1],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 0],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), 0],
            'FailedAssertion' => [self::implicitDefaultTestSuitePath('FailedAssertion'), 0],
            'ExceptionThrowingTest' => [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), 1]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectErroredTestCountProvider')]
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

    public static function processedAggregateSummaryWithCorrectAssertionCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 1],
            'MultipleTest' => [self::implicitDefaultTestSuitePath('MultipleTest'), 3],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 10],
            'ExtendedTestCases' => [self::implicitDefaultTestSuitePath('ExtendedTestCases'), 22]
        ];
    }

    #[DataProvider('processedAggregateSummaryWithCorrectAssertionCountProvider')]
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

    public static function processedTestSuiteSummaryTestSuiteNameProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class]],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class
            ]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryTestSuiteNameProvider')]
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

    public static function processedTestSuiteSummaryTestCaseNamesProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => [ImplicitDefaultTestSuite\SingleTest\MyTestCase::class]
            ]],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), [
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
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => [
                    ExplicitTestSuite\TestSuiteDisabled\FirstTestCase::class,
                    ExplicitTestSuite\TestSuiteDisabled\SecondTestCase::class
                ]
            ]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryTestCaseNamesProvider')]
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

    public static function processedTestSuiteSummaryTotalTestCaseCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 1,
            ]],
            'ExtendedTestCases' => [self::implicitDefaultTestSuitePath('ExtendedTestCases'), [
                ImplicitTestSuite::class => 3
            ]],
            'TestCaseDefinesTestSuite' => [self::explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 2
            ]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryTotalTestCaseCountProvider')]
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

    public static function processedTestSuiteSummaryDisabledTestCaseCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 0,
            ]],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            'TestCaseDefinesTestSuite' => [self::explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 2
            ]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryDisabledTestCaseCountProvider')]
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

    public static function processedTestSuiteSummaryTotalTestCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 1,
            ]],
            [self::implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 3
            ]],
            [self::explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            [self::explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 3
            ]],
            [self::implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 2
            ]],
            [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitTestSuite::class => 1
            ]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryTotalTestCountProvider')]
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

    public static function processedTestSuiteSummaryDisabledTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitTestSuite::class => 0,
            ]],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), [
                ImplicitTestSuite::class => 3
            ]],
            'TestCaseDefinesTestSuite' => [self::explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), [
                ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class => 3
            ]],
            'TestDisabled' => [self::implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            'ExceptionThrowingTest' => [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitTestSuite::class => 0
            ]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryDisabledTestCountProvider')]
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

    public static function processedTestSuiteSummaryPassedTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class => 1,]],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            'TestCaseDefinesTestSuite' => [self::explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            'ExtendedTestCases' => [self::implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 8]],
            'TestDisabled' => [self::implicitDefaultTestSuitePath('TestDisabled'), [
                ImplicitTestSuite::class => 1
            ]],
            'ExceptionThrowingTest' => [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitTestSuite::class => 0
            ]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryPassedTestCountProvider')]
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

    public static function processedTestSuiteSummaryFailedTestCountProvider() : array {
        return [
            'FailedAssertion' => [self::implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 1,]],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            'TestCaseDefinesTestSuite' => [self::explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            'ExtendedTestCases' => [self::implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 1]],
            'FailedNotAssertion' => [self::implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 1]],
            'ExceptionThrowingTest' => [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [ImplicitTestSuite::class => 0]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryFailedTestCountProvider')]
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

    public static function processedTestSuiteSummaryErroredTestCountProvider() : array {
        return [
            'FailedAssertion' => [self::implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 0]],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            'TestCaseDefinesTestSuite' => [self::explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 0,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 0
            ]],
            'ExtendedTestCases' => [self::implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 0]],
            'FailedNotAssertion' => [self::implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 0]],
            'ExceptionThrowingTest' => [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [ImplicitTestSuite::class => 1]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryErroredTestCountProvider')]
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

    public static function processedTestSuiteSummaryAssertionCountProvider() : array {
        return [
            'FailedAssertion' => [self::implicitDefaultTestSuitePath('FailedAssertion'), [ImplicitTestSuite::class => 1,]],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), [ImplicitTestSuite::class => 0]],
            'TestCaseDefinesTestSuite' => [self::explicitTestSuitePath('TestCaseDefinesTestSuite'), [
                ExplicitTestSuite\TestCaseDefinesTestSuite\MyFirstTestSuite::class => 1,
                ExplicitTestSuite\TestCaseDefinesTestSuite\MySecondTestSuite::class => 2
            ]],
            'ExtendedTestCases' => [self::implicitDefaultTestSuitePath('ExtendedTestCases'), [ImplicitTestSuite::class => 22]],
            'FailedNotAssertion' => [self::implicitDefaultTestSuitePath('FailedNotAssertion'), [ImplicitTestSuite::class => 1]]
        ];
    }

    #[DataProvider('processedTestSuiteSummaryAssertionCountProvider')]
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

    public static function processedTestCaseSummaryTestSuiteNameProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => ImplicitTestSuite::class
            ]],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), [
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

    #[DataProvider('processedTestCaseSummaryTestSuiteNameProvider')]
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

    public static function processedTestCaseSummaryTestNamesProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => [
                    ImplicitDefaultTestSuite\SingleTest\MyTestCase::class . '::ensureSomethingHappens'
                ]
            ]],
            [self::implicitDefaultTestSuitePath('KitchenSink'), [
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

    #[DataProvider('processedTestCaseSummaryTestNamesProvider')]
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

    public static function processedTestCaseSummaryTestCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            [self::implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 3,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 2,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 4,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 1
            ]],
            [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 1
            ]]
        ];
    }

    #[DataProvider('processedTestCaseSummaryTestCountProvider')]
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

    public static function processedTestCaseSummaryDisabledTestCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [self::implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 0
            ]],
            [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 0
            ]]
        ];
    }

    #[DataProvider('processedTestCaseSummaryDisabledTestCountProvider')]
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

    public static function processedTestCaseSummaryPassedTestCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            [self::implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 2,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 4,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 0
            ]],
            [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 0
            ]]
        ];
    }

    #[DataProvider('processedTestCaseSummaryPassedTestCountProvider')]
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

    public static function processedTestCaseSummaryFailedTestCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [self::implicitDefaultTestSuitePath('FailedAssertion'), [
                ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::class => 1,
            ]],
            [self::implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 1,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 0
            ]],
            [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 0
            ]]
        ];
    }

    #[DataProvider('processedTestCaseSummaryFailedTestCountProvider')]
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

    public static function processedTestCaseSummaryErroredTestCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 0
            ]],
            [self::implicitDefaultTestSuitePath('FailedAssertion'), [
                ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::class => 0,
            ]],
            [self::implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class => 0,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class => 1
            ]],
            [self::implicitDefaultTestSuitePath('ExceptionThrowingTest'), [
                ImplicitDefaultTestSuite\ExceptionThrowingTest\MyTestCase::class => 1
            ]]
        ];
    }

    #[DataProvider('processedTestCaseSummaryErroredTestCountProvider')]
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

    public static function processedTestCaseSummaryAssertionCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class => 1
            ]],
            'FailedAssertion' => [self::implicitDefaultTestSuitePath('FailedAssertion'), [
                ImplicitDefaultTestSuite\FailedAssertion\MyTestCase::class => 1,
            ]],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), [
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

    #[DataProvider('processedTestCaseSummaryAssertionCountProvider')]
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
