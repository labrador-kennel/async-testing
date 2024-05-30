<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnitCli\Command;


use Amp\ByteStream\OutputBuffer;
use Amp\ByteStream\WritableBuffer;
use Amp\File\Driver as FileDriver;
use Amp\File\Filesystem;
use Amp\File\FilesystemDriver;
use Cspray\Labrador\AsyncUnit\JsonConfigurationFactory;
use Cspray\Labrador\AsyncUnit\NoConstructorMockBridgeFactory;
use Cspray\Labrador\AsyncUnitCli\AsyncUnitConsoleApplication;
use Cspray\Labrador\AsyncUnitCli\TerminalResultPrinter;
use Labrador\AsyncEvent\AmpEventEmitter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use function Amp\File\filesystem;

abstract class BaseCommandTest extends TestCase {

    protected FilesystemDriver|MockObject|null $driver;

    protected WritableBuffer $testResultBuffer;

    protected function createApplication(string $configPath, FilesystemDriver $driver = null) : AsyncUnitConsoleApplication {
        if ($driver === null) {
            $this->driver = $this->createMock(FilesystemDriver::class);
        }
        return new AsyncUnitConsoleApplication(
            new NullLogger(),
            new AmpEventEmitter(),
            filesystem($driver ?? $this->driver),
            new JsonConfigurationFactory(),
            $this->testResultBuffer = new WritableBuffer(),
            $configPath
        );
    }

    protected function getDefaultConfigurationJson() : string {
        $expected = [
            'testDirectories' => ['./tests'],
            'resultPrinter' => TerminalResultPrinter::class,
            'plugins' => []
        ];
        return json_encode($expected, JSON_PRETTY_PRINT);
    }

}