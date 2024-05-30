<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\ByteStream\WritableBuffer;
use Cspray\Labrador\AsyncUnit\Configuration\ConfigurationFactory;
use Cspray\Labrador\AsyncUnit\MockBridge\MockeryMockBridge;
use Cspray\Labrador\AsyncUnit\Stub\TestConfiguration;
use Labrador\AsyncEvent\AmpEventEmitter;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Psr\Log\NullLogger;

class AsyncUnitFrameworkRunnerTest extends PHPUnitTestCase {

    use UsesAcmeSrc;

    public function testSinglePassingTest() {
        $logger = new NullLogger();
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('SingleTest')]);
        $configurationFactory = $this->createMock(ConfigurationFactory::class);
        $configurationFactory->expects($this->once())
            ->method('make')
            ->with('configPath')
            ->willReturn($configuration);

        $frameworkRunner = new AsyncUnitFrameworkRunner(
            $logger,
            new AmpEventEmitter(),
            $configurationFactory,
            new WritableBuffer()
        );

        $frameworkRunner->run('configPath');
    }

    public function testFailedAssertionTest() {
        $logger = new NullLogger();
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('FailedAssertion')]);
        $configurationFactory = $this->createMock(ConfigurationFactory::class);
        $configurationFactory->expects($this->once())
            ->method('make')
            ->with('configPath')
            ->willReturn($configuration);

        $frameworkRunner = new AsyncUnitFrameworkRunner(
            $logger,
            new AmpEventEmitter(),
            $configurationFactory,
            new WritableBuffer()
        );

        $frameworkRunner->run('configPath');
    }

    public function testSingleMockWithNoAssertion() {
        $logger = new NullLogger();
        $configuration = new TestConfiguration();
        $configuration->setTestDirectories([$this->implicitDefaultTestSuitePath('MockeryTestNoAssertion')]);
        $configuration->setMockBridge(MockeryMockBridge::class);
        $configurationFactory = $this->createMock(ConfigurationFactory::class);
        $configurationFactory->expects($this->once())
            ->method('make')
            ->with('configPath')
            ->willReturn($configuration);

        $frameworkRunner = new AsyncUnitFrameworkRunner(
            $logger,
            new AmpEventEmitter(),
            $configurationFactory,
            new WritableBuffer()
        );

        $frameworkRunner->run('configPath');
    }

}