<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

use Labrador\AsyncEvent\Emitter;
use Labrador\AsyncUnit\Framework\Configuration\AsyncUnitConfigurationValidator;
use Labrador\AsyncUnit\Framework\Configuration\ConfigurationFactory;
use Labrador\AsyncUnit\Framework\Configuration\JsonConfigurationFactory;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridgeFactory;
use Labrador\AsyncUnit\Framework\MockBridge\NoConstructorMockBridgeFactory;
use Labrador\AsyncUnit\Framework\Parser\StaticAnalysisParser;
use Labrador\AsyncUnit\Framework\Randomizer\ShuffleRandomizer;

/**
 * A Facade, traditional OOP definition, to easily run a series of tests based on a Configuration that could be overridden
 * based on the precise context in which the AsyncUnitFrameworkRunner is being used.
 *
 *
 *
 * @package Labrador\AsyncUnit\Framework
 */
final class AsyncUnitFrameworkRunner {

    public function __construct(
        private readonly Emitter $emitter,
        private readonly ConfigurationFactory $configurationFactory = new JsonConfigurationFactory(),
        private readonly MockBridgeFactory $mockBridgeFactory = new NoConstructorMockBridgeFactory()
    ) {}

    public function run(string $configFile) : void {
        $application = new AsyncUnitApplication(
            new AsyncUnitConfigurationValidator(),
            $this->configurationFactory,
            new StaticAnalysisParser(),
            new TestSuiteRunner(
                $this->emitter,
                new ShuffleRandomizer(),
                $this->mockBridgeFactory
            ),
            $configFile
        );

        $application->run();
    }

}