<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Amp\ByteStream\WritableBuffer;
use Labrador\AsyncUnit\Framework\AsyncUnitFrameworkRunner;
use Labrador\AsyncUnit\Framework\Configuration\ConfigurationFactory;
use Labrador\AsyncUnit\Framework\MockBridge\MockeryMockBridge;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\TestConfiguration;
use Labrador\AsyncEvent\AmpEmitter;
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
            new AmpEmitter(),
            $configurationFactory,
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
            new AmpEmitter(),
            $configurationFactory,
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
            new AmpEmitter(),
            $configurationFactory,
        );

        $frameworkRunner->run('configPath');
    }

}