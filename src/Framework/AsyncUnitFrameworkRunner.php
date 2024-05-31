<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

use Labrador\AsyncEvent\Emitter;
use Labrador\AsyncUnit\Framework\Configuration\AsyncUnitConfigurationValidator;
use Labrador\AsyncUnit\Framework\Configuration\ConfigurationFactory;
use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridgeFactory;
use Labrador\AsyncUnit\Framework\MockBridge\NoConstructorMockBridgeFactory;
use Labrador\AsyncUnit\Framework\Parser\StaticAnalysisParser;
use Labrador\AsyncUnit\Framework\Plugin\CustomAssertionPlugin;
use Labrador\AsyncUnit\Framework\Plugin\ResultPrinterPlugin;
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

    /**
     * @var list<ResultPrinterPlugin>
     */
    private array $resultPrinterPlugins = [];

    /**
     * @var list<CustomAssertionPlugin>
     */
    private array $customAssertionPlugin = [];

    public function __construct(
        private readonly Emitter $emitter,
        private readonly ConfigurationFactory $configurationFactory,
        private readonly ?MockBridgeFactory $mockBridgeFactory = null
    ) {}

    public function registerPlugin(ResultPrinterPlugin|CustomAssertionPlugin $plugin) : void {
        if ($plugin instanceof ResultPrinterPlugin) {
            $this->resultPrinterPlugins[] = $plugin;
        } else {
            $this->customAssertionPlugin[] = $plugin;
        }
    }

    public function run(string $configFile) : void {
        $application = new AsyncUnitApplication(
            new AsyncUnitConfigurationValidator(),
            $this->configurationFactory,
            new StaticAnalysisParser(),
            new TestSuiteRunner(
                $this->emitter,
                new CustomAssertionContext(),
                new ShuffleRandomizer(),
                $this->mockBridgeFactory ?? new NoConstructorMockBridgeFactory()
            ),
            $configFile
        );

        $application->run();
    }

}