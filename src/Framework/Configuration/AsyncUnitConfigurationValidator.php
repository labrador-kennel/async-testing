<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Configuration;

use Amp\File\Filesystem;
use Labrador\AsyncUnit\Framework\Plugin\ResultPrinterPlugin;
use Labrador\AsyncUnit\Framework\ResultPrinter;
use function Amp\File\filesystem;

final class AsyncUnitConfigurationValidator implements ConfigurationValidator {

    private readonly Filesystem $filesystem;

    public function __construct() {
        $this->filesystem = filesystem();
    }

    public function validate(Configuration $configuration): ConfigurationValidationResults {
        $errors = [
            ...$this->validateTestDirectories($configuration),
            ...$this->validateResultPrinterClass($configuration)
        ];
        return new ConfigurationValidationResults($errors);
    }

    private function validateTestDirectories(Configuration $configuration) : array {
        $errors = [];
        $testDirs = $configuration->getTestDirectories();
        foreach ($testDirs as $testDir) {
            if (!$this->filesystem->isDirectory($testDir)) {
                if (!isset($errors['testDirectories'])) {
                    $errors['testDirectories'] = [];
                }
                $errors['testDirectories'][] = sprintf(
                    'A configured directory to scan, "%s", is not a directory.',
                    $testDir
                );
            }
        }

        return $errors;
    }

    private function validateResultPrinterClass(Configuration $configuration) : array {
        $errors = [];
        $resultPrinterClass = $configuration->getResultPrinter();
        if (!class_exists($resultPrinterClass)) {
            $errors['resultPrinter'] = [sprintf(
                'The result printer "%s" is not a class that can be found. Please ensure this class is configured to be autoloaded through Composer.',
                $resultPrinterClass
            )];
        } else if (!in_array(ResultPrinter::class, class_implements($resultPrinterClass), true)) {
            $errors['resultPrinter'] = [sprintf(
                'The result printer "%s" is not a %s. Please ensure your result printer implements this interface.',
                $resultPrinterClass,
                ResultPrinter::class
            )];
        }
        return $errors;
    }
}