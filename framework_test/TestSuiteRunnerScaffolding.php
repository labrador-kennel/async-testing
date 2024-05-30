<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Parser\StaticAnalysisParser;
use Labrador\AsyncEvent\AmpEventEmitter;
use Labrador\AsyncEvent\EventEmitter;
use ReflectionClass;

trait TestSuiteRunnerScaffolding {

    private StaticAnalysisParser $parser;
    private EventEmitter $emitter;
    private CustomAssertionContext $customAssertionContext;
    private TestSuiteRunner $testSuiteRunner;
    private MockBridgeFactory $mockBridgeFactory;

    public function buildTestSuiteRunner() : void {
        $this->parser = new StaticAnalysisParser();
        $this->emitter = new AmpEventEmitter();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->customAssertionContext = (new ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $this->mockBridgeFactory = new NoConstructorMockBridgeFactory($this->getMockBuilder(AutowireableFactory::class)->getMock());
        $this->testSuiteRunner = new TestSuiteRunner(
            $this->emitter,
            $this->customAssertionContext,
            new NullRandomizer(),
            $this->mockBridgeFactory
        );
    }

}