<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\ByteStream\OutputBuffer;
use Amp\Success;
use Auryn\Injector;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncEvent\EventEmitter;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Event\TestFailedEvent;
use Cspray\Labrador\AsyncUnit\Event\TestPassedEvent;
use Cspray\Labrador\AsyncUnit\Exception\InvalidConfigurationException;
use Cspray\Labrador\AsyncUnit\Stub\BarAssertionPlugin;
use Cspray\Labrador\AsyncUnit\Stub\FooAssertionPlugin;
use Cspray\Labrador\AsyncUnit\Stub\MockBridgeStub;
use Cspray\Labrador\AsyncUnit\Stub\TestConfiguration;
use Cspray\Labrador\Engine;
use Cspray\Labrador\EnvironmentType;
use Cspray\Labrador\StandardEnvironment;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\NullLogger;
use stdClass;

class AsyncUnitApplicationTest extends \PHPUnit\Framework\TestCase {

    use UsesAcmeSrc;

    private Injector $injector;

    private MockBridgeFactory|MockObject $mockBridgeFactory;

    private MockBridgeStub $mockBridgeStub;

    private function getStateAndApplication(
        string $configPath,
        Configuration $configuration
    ) : array {
        $environment = new StandardEnvironment(EnvironmentType::Test());
        $logger = new NullLogger();
        $configurationFactory = $this->createMock(ConfigurationFactory::class);
        $configurationFactory->expects($this->once())
            ->method('make')
            ->with($configPath)
            ->willReturn(new Success($configuration));

        $this->mockBridgeStub = new MockBridgeStub();
        $this->mockBridgeFactory = $this->createMock(MockBridgeFactory::class);

        $objectGraph = (new AsyncUnitApplicationObjectGraph(
            $environment,
            $logger,
            $configurationFactory,
            new OutputBuffer(),
            $configPath,
            $this->mockBridgeFactory,
        ))->wireObjectGraph();
        $objectGraph->alias(Randomizer::class, NullRandomizer::class);

        $emitter = $objectGraph->make(EventEmitter::class);
        $this->injector = $objectGraph;

        $state = new stdClass();
        $state->data = [];
        $state->passed = new stdClass();
        $state->passed->events = [];
        $state->failed = new stdClass();
        $state->failed->events = [];
        $state->disabled = new stdClass();
        $state->disabled->events = [];
        $emitter->on(Events::TEST_PASSED, function($event) use($state) {
            $state->passed->events[] = $event;
        });
        $emitter->on(Events::TEST_FAILED, function($event) use($state) {
            $state->failed->events[] = $event;
        });
        $emitter->on(Events::TEST_DISABLED, function($event) use($state) {
            $state->disabled->events[] = $event;
        });

        return [$state, $this->injector->make(Engine::class)];
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTest() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('SingleTest')]);
        [$state, $engine] = $this->getStateAndApplication('singleTest', $configuration);
        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);
        /** @var TestPassedEvent $event */
        $event = $state->passed->events[0];
        $this->assertInstanceOf(TestPassedEvent::class, $event);

        $testResult = $event->getTarget();

        $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, $testResult->getTestCase());
        $this->assertSame('ensureSomethingHappens', $testResult->getTestMethod());
        $this->assertSame(TestState::Passed, $testResult->getState());
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteSingleTestAsyncAssertion() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('SingleTestAsyncAssertion')]);
        [$state, $engine] = $this->getStateAndApplication('singleTestAsync', $configuration);
        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);
        /** @var TestPassedEvent $event */
        $event = $state->passed->events[0];
        $this->assertInstanceOf(TestPassedEvent::class, $event);

        $testResult = $event->getTarget();

        $this->assertInstanceOf(ImplicitDefaultTestSuite\SingleTestAsyncAssertion\MyTestCase::class, $testResult->getTestCase());
        $this->assertSame('ensureAsyncAssert', $testResult->getTestMethod());
        $this->assertSame(TestState::Passed, $testResult->getState());
    }

    public function testSimpleTestCaseImplicitDefaultTestSuiteNoAssertions() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('NoAssertions')]);
        [$state, $engine] = $this->getStateAndApplication('noAssertions', $configuration);
        $engine->run($this->injector->make(Application::class));

        $this->assertCount(0, $state->passed->events);
        $this->assertCount(1, $state->failed->events);
        /** @var TestFailedEvent $event */
        $event = $state->failed->events[0];
        $this->assertInstanceOf(TestFailedEvent::class, $event);

        $testResult = $event->getTarget();

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
        [$state, $engine] = $this->getStateAndApplication('failedAssertion', $configuration);
        $engine->run($this->injector->make(Application::class));

        $this->assertCount(0, $state->passed->events);
        $this->assertCount(1, $state->failed->events);
        /** @var TestFailedEvent $event */
        $event = $state->failed->events[0];
        $this->assertInstanceOf(TestFailedEvent::class, $event);

        $testResult = $event->getTarget();
        $this->assertSame(TestState::Failed, $testResult->getState());
    }

    public function testLoadingCustomAssertionPlugins() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('SingleTest')]);
        [,$engine] = $this->getStateAndApplication('singleTest', $configuration);

        $this->injector->share(FooAssertionPlugin::class);
        $this->injector->share(BarAssertionPlugin::class);

        $application = $this->injector->make(Application::class);

        $application->registerPlugin(FooAssertionPlugin::class);
        $application->registerPlugin(BarAssertionPlugin::class);

        $engine->run($application);

        $actual = $this->injector->make(CustomAssertionContext::class);

        $fooPlugin = $this->injector->make(FooAssertionPlugin::class);
        $barPlugin = $this->injector->make(BarAssertionPlugin::class);

        $this->assertSame($fooPlugin->getCustomAssertionContext(), $actual);
        $this->assertSame($barPlugin->getCustomAssertionContext(), $actual);
    }

    public function testExplicitTestSuiteTestSuiteStateShared() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->explicitTestSuitePath('TestSuiteStateBeforeAll')]);
        [$state, $engine] = $this->getStateAndApplication('testSuiteBeforeAll', $configuration);

        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);
    }

    public function testExplicitTestSuiteTestCaseBeforeAllHasTestSuiteState() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->explicitTestSuitePath('TestCaseBeforeAllHasTestSuiteState')]);
        [$state, $engine] = $this->getStateAndApplication('testCaseBeforeAllHasTestSuiteState', $configuration);

        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);
    }

    public function testExplicitTestSuiteTestCaseAfterAllHasTestSuiteState() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->explicitTestSuitePath('TestCaseAfterAllHasTestSuiteState')]);
        [$state, $engine] = $this->getStateAndApplication('testCaseAfterAllHasTestSuiteState', $configuration);

        $engine->run($this->injector->make(Application::class));

        $this->assertCount(1, $state->passed->events);
        $this->assertCount(0, $state->failed->events);

        $this->assertSame('AsyncUnit', $state->passed->events[0]->getTarget()->getTestCase()->getState());
    }

    public function testConfigurationInvalidThrowsException() {
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([]);
        $configuration->setResultPrinterClass('Not a class');
        [, $engine] = $this->getStateAndApplication('invalidConfig', $configuration);

        $this->expectException(InvalidConfigurationException::class);
        $expectedMessage = <<<'msg'
The configuration at path "invalidConfig" has the following errors:

- Must provide at least one directory to scan but none were provided.
- The result printer "Not a class" is not a class that can be found. Please ensure this class is configured to be autoloaded through Composer.

Please fix the errors listed above and try running your tests again.
msg;
        $this->expectExceptionMessage($expectedMessage);

        $engine->run($this->injector->make(Application::class));
    }

}