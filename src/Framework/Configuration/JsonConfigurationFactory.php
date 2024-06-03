<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Configuration;

use Amp\File\Filesystem;
use Labrador\AsyncUnit\Framework\Exception\InvalidConfigurationException;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\Uri;
use Opis\JsonSchema\Validator;
use stdClass;
use function Amp\File\filesystem;

final class JsonConfigurationFactory implements ConfigurationFactory {

    private readonly Validator $validator;
    private readonly Schema $schema;
    private readonly Filesystem $filesystem;

    public function __construct() {
        $this->validator = new Validator();
        $resolver = $this->validator->resolver();
        assert($resolver !== null);
        $resolver->registerFile(
            'https://labrador-kennel.io/dev/async-unit/schema/cli-config.json',
            dirname(__DIR__, 3) . '/resources/schema/cli-config.json'
        );
        $schema = $this->validator->loader()->loadSchemaById(
            Uri::parse('https://labrador-kennel.io/dev/async-unit/schema/cli-config.json')
        );
        if (is_null($schema)) {
            throw new InvalidConfigurationException('Could not locate the schema for validating CLI configurations');
        }
        $this->schema = $schema;
        $this->filesystem = filesystem();
    }

    public function make(string $path) : Configuration {
        $contents = $this->filesystem->read($path);
        $configJson = json_decode($contents, flags: JSON_THROW_ON_ERROR);
        $results = $this->validator->validate($configJson, $this->schema);
        if ($results->hasError()) {
            $msg = sprintf(
                'The JSON file at "%s" does not adhere to the JSON Schema https://labrador-kennel.io/dev/async-unit/schema/cli-config.json',
                $path
            );
            throw new InvalidConfigurationException($msg);
        }

        $absoluteTestDirs = [];
        foreach ($configJson->testDirectories as $testDir) {
            $absoluteTestDirs[] = realpath($testDir);
        }
        $configJson->testDirectories = $absoluteTestDirs;

        return new class($configJson) implements Configuration {

            public function __construct(private readonly stdClass $config) {}

            public function getTestDirectories() : array {
                return $this->config->testDirectories;
            }

            public function getResultPrinter(): string {
                return $this->config->resultPrinter;
            }

            public function getMockBridge(): ?string {
                return $this->config->mockBridge ?? null;
            }
        };
    }

}