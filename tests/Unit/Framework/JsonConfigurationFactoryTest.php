<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Labrador\AsyncUnit\Framework\Configuration\JsonConfigurationFactory;
use Labrador\AsyncUnit\Framework\Exception\InvalidConfigurationException;
use Labrador\AsyncUnit\Framework\MockBridge\MockeryMockBridge;
use Labrador\AsyncUnit\Cli\TerminalResultPrinter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class JsonConfigurationFactoryTest extends TestCase {

    private JsonConfigurationFactory $subject;

    public function setUp() : void {
        $this->subject = new JsonConfigurationFactory();
    }

    public static function badSchemaProvider() : array {
        return [
            'empty_object' => [__DIR__ . '/Resources/dummy_configs/empty_object.json'],
            'bad_keys' => [__DIR__ . '/Resources/dummy_configs/bad_keys.json'],
            'good_keys_bad_types' => [__DIR__ . '/Resources/dummy_configs/good_keys_bad_types.json'],
            'test_dirs_empty' => [__DIR__ . '/Resources/dummy_configs/test_dirs_empty.json'],
            'test_dirs_non_string' => [__DIR__ . '/Resources/dummy_configs/test_dirs_non_string.json'],
            'test_dirs_empty_string' => [__DIR__ . '/Resources/dummy_configs/test_dirs_empty_string.json'],
            'good_keys_but_extra' => [__DIR__ . '/Resources/dummy_configs/good_keys_but_extra.json'],
            'result_printer_null' => [__DIR__ . '/Resources/dummy_configs/result_printer_null.json'],
            'result_printer_empty' => [__DIR__ . '/Resources/dummy_configs/result_printer_empty.json'],
            'mock_bridge_empty_string' => [__DIR__ . '/Resources/dummy_configs/mock_bridge_empty_string.json']
        ];
    }

    #[DataProvider('badSchemaProvider')]
    public function testBadSchemaThrowsException(string $file) {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage(sprintf(
            'The JSON file at "%s" does not adhere to the JSON Schema https://labrador-kennel.io/dev/async-unit/schema/cli-config.json',
            $file
        ));

        $this->subject->make($file);
    }

    public function testMinimallyValidReturnsCorrectInformation() {
        $configuration = $this->subject->make(__DIR__ . '/Resources/dummy_configs/minimally_valid.json');

        $this->assertSame([getcwd()], $configuration->getTestDirectories());
        $this->assertSame(TerminalResultPrinter::class, $configuration->getResultPrinter());
    }

    public function testHasMockBridgeReturnsCorrectInformation() {
        $configuration = $this->subject->make(__DIR__ . '/Resources/dummy_configs/has_mock_bridge.json');

        $this->assertSame([getcwd()], $configuration->getTestDirectories());
        $this->assertSame(MockeryMockBridge::class, $configuration->getMockBridge());
    }

}