<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Labrador\AsyncEvent\AmpEmitter;
use Labrador\AsyncEvent\Emitter;
use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridgeFactory;
use Labrador\AsyncUnit\Framework\MockBridge\NoConstructorMockBridgeFactory;
use Labrador\AsyncUnit\Framework\Parser\StaticAnalysisParser;
use Labrador\AsyncUnit\Framework\Randomizer\NullRandomizer;
use Labrador\AsyncUnit\Framework\TestSuiteRunner;
use ReflectionClass;

trait TestSuiteRunnerScaffolding {

    private StaticAnalysisParser $parser;
    private Emitter $emitter;
    private TestSuiteRunner $testSuiteRunner;
    private MockBridgeFactory $mockBridgeFactory;

    public function buildTestSuiteRunner() : void {
        $this->parser = new StaticAnalysisParser();
        $this->emitter = new AmpEmitter();
        $this->mockBridgeFactory = new NoConstructorMockBridgeFactory();
        $this->testSuiteRunner = new TestSuiteRunner(
            $this->emitter,
            new NullRandomizer(),
            $this->mockBridgeFactory
        );
    }

}