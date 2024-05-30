<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Stub;

use Cspray\Labrador\AsyncUnit\Configuration\Configuration;
use Cspray\Labrador\AsyncUnitCli\TerminalResultPrinter;

class TestConfiguration implements Configuration {

    private array $testDirectories = [];

    private array $plugins = [];

    private string $resultPrinterClass = TerminalResultPrinter::class;

    private ?string $mockBridge = null;

    public function __construct() {}

    public function setTestDirectories(array $testDirs) : void {
        $this->testDirectories = $testDirs;
    }

    public function getTestDirectories(): array {
        return $this->testDirectories;
    }

    public function setPlugins(array $plugins) : void {
        $this->plugins = $plugins;
    }

    public function getPlugins(): array {
        return $this->plugins;
    }

    public function setResultPrinterClass(string $resultPrinterClass) : void {
        $this->resultPrinterClass = $resultPrinterClass;
    }

    public function getResultPrinter(): string {
        return $this->resultPrinterClass;
    }

    public function setMockBridge(string $mockBridge) : void {
        $this->mockBridge = $mockBridge;
    }

    public function getMockBridge(): ?string {
        return $this->mockBridge;
    }
}