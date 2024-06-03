<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Command;

use Amp\File\Filesystem;
use Cspray\Labrador\AsyncUnit\AsyncUnitFrameworkRunner;
use Revolt\EventLoop;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RunTestsCommand extends AbstractCommand {

    public function __construct(
        private Filesystem $fileDriver,
        private AsyncUnitFrameworkRunner $frameworkRunner,
        private string $defaultConfigFile
    ) {
        parent::__construct();
    }

    protected function configure() {
        $this->addOption('config', mode: InputOption::VALUE_REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output) : int {
        $runTests = true;
        $configPath = $input->getOption('config') ?? $this->defaultConfigFile;
        if (!$this->fileDriver->isFile($configPath)) {
            $style = new SymfonyStyle($input, $output);
            $style->writeln($this->getNoDefaultConfigurationMessage());
            $style->newLine();

            $generateConfig = $this->confirm(
                $input,
                $output,
                'Would you like to run \'config:generate\' now?',
                true
            );

            if (!$generateConfig) {
                $style->writeln('Ok! Exiting.');
                $runTests = false;
            } else {
                $generateConfigCommand = $this->getApplication()->find('config:generate');
                $args = new ArrayInput([
                    '--file' => $configPath
                ]);
                $generateConfigCommand->run($args, $output);
            }
        }

        if ($runTests) {
            $this->frameworkRunner->run($configPath);
        }

        return Command::SUCCESS;
    }

    private function getNoDefaultConfigurationMessage() : string {
        return <<<'shell'
Running AsyncUnit tests without a configuration is no longer supported. You can run the 'config:generate' command to 
create a default configuration and get started quickly. Otherwise please pass a file path to the --config option.
shell;
    }

    protected function getCommandName(): string {
        return 'run';
    }
}