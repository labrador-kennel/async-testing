<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli;

use Amp\ByteStream\OutputStream;
use Amp\ByteStream\WritableStream;
use Amp\File\Filesystem;
use Cspray\Labrador\AsyncUnit\AsyncUnitApplication;
use Cspray\Labrador\AsyncUnit\AsyncUnitFrameworkRunner;
use Cspray\Labrador\AsyncUnit\Configuration\ConfigurationFactory;
use Cspray\Labrador\AsyncUnitCli\Command\GenerateConfigurationCommand;
use Cspray\Labrador\AsyncUnitCli\Command\RunTestsCommand;
use Cspray\Labrador\Environment;
use Labrador\AsyncEvent\EventEmitter;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Application as ConsoleApplication;

final class AsyncUnitConsoleApplication extends ConsoleApplication {

    public function __construct(
        private LoggerInterface $logger,
        private EventEmitter $emitter,
        private Filesystem $fileDriver,
        private ConfigurationFactory $configurationFactory,
        private WritableStream $testResultOutput,
        private string $configPath
    ) {
        parent::__construct('AsyncUnit', AsyncUnitApplication::VERSION);
        $this->registerCommands();
    }

    private function registerCommands() {
        $frameworkRunner = new AsyncUnitFrameworkRunner(
            $this->logger,
            $this->emitter,
            $this->configurationFactory,
            $this->testResultOutput
        );
        $this->add(new RunTestsCommand($this->fileDriver, $frameworkRunner, $this->configPath));
        $this->add(new GenerateConfigurationCommand($this->fileDriver, $this->configPath));
        $this->setDefaultCommand('run');

    }

}