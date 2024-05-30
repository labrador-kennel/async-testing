<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Command;

use Amp\File\Driver\BlockingFilesystemDriver;
use Amp\Future;
use Cspray\Labrador\AsyncUnit\UsesAcmeSrc;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use function Amp\File\filesystem;

class RunTestsCommandTest extends BaseCommandTest {

    use UsesAcmeSrc;

    public function testRunningCommandWithNoConfigurationPromptsToGenerateOne() {
        $application = $this->createApplication($configPath = __DIR__ . '/not-found.json');
        $this->driver->expects($this->once())
            ->method('getStatus')
            ->with($configPath)
            ->willReturn(null);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(["n"]);
        $commandTester->execute([]);
        $actual = $commandTester->getDisplay();
        $expected = <<<'shell'
Running AsyncUnit tests without a configuration is no longer supported. You can run the 'config:generate' command to 
create a default configuration and get started quickly. Otherwise please pass a file path to the --config option.

Would you like to run 'config:generate' now? (Y/n) Ok! Exiting.

shell;
        $this->assertSame($expected, $actual);
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testConfigOptionOverridesLocationOfConfigurationFile() {
        $application = $this->createApplication(__DIR__ . '/not-found.json');
        $this->driver->expects($this->once())
            ->method('getStatus')
            ->with('/my/overridden/path')
            ->willReturn(null);

        $command = $application->find('run');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(["n"]);
        $commandTester->execute([
            '--config' => '/my/overridden/path'
        ]);
        $actual = $commandTester->getDisplay();
        $expected = <<<'shell'
Running AsyncUnit tests without a configuration is no longer supported. You can run the 'config:generate' command to 
create a default configuration and get started quickly. Otherwise please pass a file path to the --config option.

Would you like to run 'config:generate' now? (Y/n) Ok! Exiting.

shell;
        $this->assertSame($expected, $actual);
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testRunningCommandWithNoConfigurationGeneratesWhenPromptAnswersYes() {
        return $this->markTestSkipped('This will require a more thorough solution for file system mocking.');
        $application = $this->createApplication($configPath = __DIR__ . '/not-found.json');
        $this->driver->expects($this->exactly(2))
            ->method('isfile')
            ->with($configPath)
            ->willReturn(new Success(false));

        $this->driver->expects($this->once())
            ->method('put')
            ->with($configPath, $this->getDefaultConfigurationJson())
            ->willReturn(new Success());

        $command = $application->find('run');
        $commandTester = new CommandTester($command);
        $commandTester->setInputs([""]);
        $commandTester->execute([]);
        $actual = $commandTester->getDisplay();
        $expected = <<<shell
Running AsyncUnit tests without a configuration is no longer supported. You can run the 'config:generate' command to 
create a default configuration and get started quickly. Otherwise please pass a file path to the --config option.

Would you like to run 'config:generate' now? (Y/n) Ok! Configuration created at $configPath.

shell;
        $this->assertSame($expected, $actual);
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());
    }

    public function testRunningCommandWithConfigRunsTests() {
        $application = $this->createApplication(
            $this->implicitDefaultTestSuitePath('SingleTest/async-unit.json'),
            new BlockingFilesystemDriver()
        );

        $command = $application->find('run');
        $commandTester = new CommandTester($command);
        $commandTester->execute([]);
        $actual = $commandTester->getDisplay();
        $this->assertSame('', $actual);
        $this->assertSame(Command::SUCCESS, $commandTester->getStatusCode());

        $testResultOutput = $this->testResultBuffer->buffer();
        // Actual style of output will be tested in an integration test
        $this->assertNotEmpty($testResultOutput);
    }

}