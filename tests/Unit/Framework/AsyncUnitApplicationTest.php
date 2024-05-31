<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Amp\Future;
use Labrador\AsyncEvent\AmpEmitter;
use Labrador\AsyncEvent\Listener;
use Labrador\AsyncUnit\Framework\AsyncUnitApplication;
use Labrador\AsyncUnit\Framework\Configuration\AsyncUnitConfigurationValidator;
use Labrador\AsyncUnit\Framework\Configuration\Configuration;
use Labrador\AsyncUnit\Framework\Configuration\ConfigurationFactory;
use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Framework\Event\Events;
use Labrador\AsyncUnit\Framework\Event\TestFailedEvent;
use Labrador\AsyncUnit\Framework\Event\TestPassedEvent;
use Labrador\AsyncUnit\Framework\Exception\InvalidConfigurationException;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridgeFactory;
use Labrador\AsyncUnit\Framework\Parser\StaticAnalysisParser;
use Labrador\AsyncUnit\Framework\Randomizer\ShuffleRandomizer;
use Labrador\AsyncUnit\Framework\TestState;
use Labrador\AsyncUnit\Framework\TestSuiteRunner;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\BarAssertionPlugin;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\FooAssertionPlugin;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\MockBridgeStub;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\TestConfiguration;
use Labrador\AsyncEvent\Event;
use Labrador\CompositeFuture\CompositeFuture;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

class AsyncUnitApplicationTest extends TestCase {

    use UsesAcmeSrc;

    private MockBridgeFactory|MockObject $mockBridgeFactory;

    private MockBridgeStub $mockBridgeStub;

    /**
     * @return array{0: stdClass, 1: AsyncUnitApplication}
     */
    private function getStateAndApplication(
        string $configPath,
        Configuration $configuration
    ) : array {
        $configurationFactory = $this->createMock(ConfigurationFactory::class);
        $configurationFactory->expects($this->once())
            ->method('make')
            ->with($configPath)
            ->willReturn($configuration);

        $this->mockBridgeStub = new MockBridgeStub();
        $this->mockBridgeFactory = $this->createMock(MockBridgeFactory::class);

        $emitter = new AmpEmitter();

        $application = new AsyncUnitApplication(
            new AsyncUnitConfigurationValidator(),
            $configurationFactory,
            new StaticAnalysisParser(),
            new TestSuiteRunner(
                $emitter,
                new CustomAssertionContext(),
                new ShuffleRandomizer(),
                $this->mockBridgeFactory
            ),
            $configPath
        );

        $state = new stdClass();
        $state->events = [
            Events::TEST_DISABLED => [],
            Events::TEST_PASSED => [],
            Events::TEST_FAILED => [],
            Events::TEST_ERRORED => [],
        ];

        $listener = new class($state) implements Listener {

            public function __construct(private readonly stdClass $data) {}

            public function handle(Event $event) : Future|CompositeFuture|null {
                $this->data->events[$event->name()][] = $event;
                return null;
            }
        };
        $emitter->register(Events::TEST_PASSED, $listener);
        $emitter->register(Events::TEST_FAILED, $listener);
        $emitter->register(Events::TEST_DISABLED, $listener);
        $emitter->register(Events::TEST_ERRORED, $listener);

        return [$state, $application];
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTest() : void {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('SingleTest')]);
        [$state, $application] = $this->getStateAndApplication('singleTest', $configuration);

        $application->run();

        $this->assertCount(1, $state->events[Events::TEST_PASSED]);
        $this->assertCount(0, $state->events[Events::TEST_FAILED]);
        /** @var TestPassedEvent $event */
        $event = $state->events[Events::TEST_PASSED][0];
        $this->assertInstanceOf(TestPassedEvent::class, $event);

        $testResult = $event->payload();

        $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $testResult->getTestCase());
        $this->assertSame('ensureSomethingHappens', $testResult->getTestMethod());
        $this->assertSame(TestState::Passed, $testResult->getState());
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteNoAssertions() : void {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('NoAssertions')]);
        [$state, $application] = $this->getStateAndApplication('noAssertions', $configuration);

        $application->run();

        $this->assertCount(0, $state->events[Events::TEST_PASSED]);
        $this->assertCount(1, $state->events[Events::TEST_FAILED]);
        /** @var TestFailedEvent $event */
        $event = $state->events[Events::TEST_FAILED][0];
        $this->assertInstanceOf(TestFailedEvent::class, $event);

        $testResult = $event->payload();

        $this->assertInstanceOf(ImplicitDefaultTestSuite\NoAssertions\MyTestCase::class, $testResult->getTestCase());
        $this->assertSame('noAssertions', $testResult->getTestMethod());
        $this->assertSame(TestState::Failed, $testResult->getState());
        $msg = sprintf(
            'Expected "%s::%s" #[Test] to make at least 1 Assertion but none were made.',
            ImplicitDefaultTestSuite\NoAssertions\MyTestCase::class,
            'noAssertions'
        );
        $this->assertSame($msg, $testResult->getException()->getMessage());
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteFailedAssertion() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('FailedAssertion')]);
        [$state, $application] = $this->getStateAndApplication('failedAssertion', $configuration);

        $application->run();

        $this->assertCount(0, $state->events[Events::TEST_PASSED]);
        $this->assertCount(1, $state->events[Events::TEST_FAILED]);
        /** @var TestFailedEvent $event */
        $event = $state->events[Events::TEST_FAILED][0];
        $this->assertInstanceOf(TestFailedEvent::class, $event);

        $testResult = $event->payload();
        $this->assertSame(TestState::Failed, $testResult->getState());
    }

    public function testLoadingCustomAssertionPlugins() {
        $this->markTestSkipped('Need to consider how AsyncUnit integrates with the container.');
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('SingleTest')]);
        [,$application] = $this->getStateAndApplication('singleTest', $configuration);

        $application->registerPlugin(FooAssertionPlugin::class);
        $application->registerPlugin(BarAssertionPlugin::class);

        $application->run();

        $actual = $this->injector->make(CustomAssertionContext::class);

        $fooPlugin = $this->injector->make(FooAssertionPlugin::class);
        $barPlugin = $this->injector->make(BarAssertionPlugin::class);

        $this->assertSame($fooPlugin->getCustomAssertionContext(), $actual);
        $this->assertSame($barPlugin->getCustomAssertionContext(), $actual);
    }

    public function testExplicitTestSuiteTestSuiteStateShared() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->explicitTestSuitePath('TestSuiteStateBeforeAll')]);
        [$state, $application] = $this->getStateAndApplication('testSuiteBeforeAll', $configuration);

        $application->run();

        $this->assertCount(1, $state->events[Events::TEST_PASSED]);
        $this->assertCount(0, $state->events[Events::TEST_FAILED]);
    }

    public function testExplicitTestSuiteTestCaseBeforeAllHasTestSuiteState() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->explicitTestSuitePath('TestCaseBeforeAllHasTestSuiteState')]);
        [$state, $application] = $this->getStateAndApplication('testCaseBeforeAllHasTestSuiteState', $configuration);

        $application->run();

        $this->assertCount(1, $state->events[Events::TEST_PASSED]);
        $this->assertCount(0, $state->events[Events::TEST_FAILED]);
    }

    public function testExplicitTestSuiteTestCaseAfterAllHasTestSuiteState() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->explicitTestSuitePath('TestCaseAfterAllHasTestSuiteState')]);
        [$state, $application] = $this->getStateAndApplication('testCaseAfterAllHasTestSuiteState', $configuration);

        $application->run();

        $this->assertCount(1, $state->events[Events::TEST_PASSED]);
        $this->assertCount(0, $state->events[Events::TEST_FAILED]);

        $this->assertSame('AsyncUnit', $state->events[Events::TEST_PASSED][0]->payload()->getTestCase()->getState());
    }

    public function testConfigurationInvalidThrowsException() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([]);
        $configuration->setResultPrinterClass('Not a class');
        [, $application] = $this->getStateAndApplication('invalidConfig', $configuration);

        $this->expectException(InvalidConfigurationException::class);
        $expectedMessage = <<<'msg'
The configuration at path "invalidConfig" has the following errors:

- Must provide at least one directory to scan but none were provided.
- The result printer "Not a class" is not a class that can be found. Please ensure this class is configured to be autoloaded through Composer.

Please fix the errors listed above and try running your tests again.
msg;
        $this->expectExceptionMessage($expectedMessage);
        $application->run();
    }

}