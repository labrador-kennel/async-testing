<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

use Labrador\AsyncUnit\Framework\Configuration\Configuration;
use Labrador\AsyncUnit\Framework\Configuration\ConfigurationFactory;
use Labrador\AsyncUnit\Framework\Configuration\ConfigurationValidator;
use Labrador\AsyncUnit\Framework\Exception\InvalidConfigurationException;
use Labrador\AsyncUnit\Framework\Parser\Parser;

final class AsyncUnitApplication {

    public const VERSION = '0.6.0-dev';

    private ConfigurationValidator $configurationValidator;
    private ConfigurationFactory $configurationFactory;
    private Parser $parser;
    private TestSuiteRunner $testSuiteRunner;
    private string $configFilePath;

    public function __construct(
        ConfigurationValidator $configurationValidator,
        ConfigurationFactory $configurationFactory,
        Parser $parser,
        TestSuiteRunner $testSuiteRunner,
        string $configFilePath
    ) {
        $this->configurationFactory = $configurationFactory;
        $this->configurationValidator = $configurationValidator;
        $this->parser = $parser;
        $this->testSuiteRunner = $testSuiteRunner;
        $this->configFilePath = $configFilePath;
    }

    public function run() : void {
        $configuration = $this->configurationFactory->make($this->configFilePath);
        $this->validateConfiguration($configuration);
        $parserResults = $this->parser->parse($configuration->getTestDirectories());

        gc_collect_cycles();

        $this->testSuiteRunner->setMockBridgeClass($configuration->getMockBridge());
        $this->testSuiteRunner->runTestSuites($parserResults);
    }

    private function validateConfiguration(Configuration $configuration) : void {
        $validationResults = $this->configurationValidator->validate($configuration);
        if (!$validationResults->isValid()) {
            $firstLine = sprintf(
                "The configuration at path \"%s\" has the following errors:\n\n",
                $this->configFilePath
            );
            $errorMessages = [];
            foreach ($validationResults->getValidationErrors() as $messages) {
                $errorMessages = [
                    ...$errorMessages,
                    ...array_map(static fn(string $msg) => "- $msg", $messages)
                ];
            }
            $errorList = implode(
                PHP_EOL,
                $errorMessages
            );
            $lastLine = "\n\nPlease fix the errors listed above and try running your tests again.";

            throw new InvalidConfigurationException(sprintf('%s%s%s', $firstLine, $errorList, $lastLine));
        }
    }

}
