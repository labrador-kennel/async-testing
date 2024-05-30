<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\ByteStream\WritableStream;
use Cspray\Labrador\AsyncUnit\Configuration\AsyncUnitConfigurationValidator;
use Cspray\Labrador\AsyncUnit\Configuration\ConfigurationFactory;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Parser\StaticAnalysisParser;
use Labrador\AsyncEvent\EventEmitter;
use Psr\Log\LoggerInterface;

/**
 * A Facade, traditional OOP definition, to easily run a series of tests based on a Configuration that could be overridden
 * based on the precise context in which the AsyncUnitFrameworkRunner is being used.
 *
 *
 *
 * @package Cspray\Labrador\AsyncUnit
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
        private readonly LoggerInterface $logger,
        private readonly EventEmitter $emitter,
        private readonly ConfigurationFactory $configurationFactory,
        private readonly WritableStream $testResultOutput,
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