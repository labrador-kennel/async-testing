<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Command;

use Amp\File\Filesystem;
use Cspray\Labrador\AsyncUnitCli\TerminalResultPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateConfigurationCommand extends AbstractCommand {

    public function __construct(
        private Filesystem $fileDriver,
        private string $configPath
    ) {
        parent::__construct();
    }

    protected function configure() {
        $this->addOption('file', mode: InputOption::VALUE_REQUIRED);
    }

    public function execute(InputInterface $input, OutputInterface $output) : int {
        $filePath = $input->getOption('file') ?? $this->configPath;
        if ($this->fileDriver->isFile($filePath)) {
            $output->writeln(sprintf(
                'A configuration already exists at %s.',
                $filePath
            ));
            $replace = $this->confirm(
                $input,
                $output,
                'Would you like to create a new configuration?'
            );
            if (!$replace) {
                $output->writeln('Ok! No configuration was created.');
                return Command::SUCCESS;
            }

            $output->writeln(sprintf(
                'Previous configuration moved to %s.',
                $filePath . '.bak'
            ));
            $this->fileDriver->move($filePath, $filePath . '.bak');
        }
        $config = [
            'testDirectories' => ['./tests'],
            'resultPrinter' => TerminalResultPrinter::class,
            'plugins' => []
        ];
        $this->fileDriver->write($filePath, json_encode($config, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
        $output->writeln(sprintf('Ok! Configuration created at %s.', $filePath));
        return Command::SUCCESS;
    }

    protected function getCommandName(): string {
        return 'config:generate';
    }
}