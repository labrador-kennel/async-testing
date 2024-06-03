<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Test\Unit\Framework\MockBridge;


use Labrador\AsyncUnit\Framework\Configuration\Configuration;
use Labrador\AsyncUnit\Framework\Exception\MockFailureException;
use Labrador\AsyncUnit\Framework\MockBridge\MockeryMockBridge;
use PHPUnit\Framework\TestCase;

class MockeryMockBridgeTest extends TestCase {

    public function testMockWithBadPredictions() : void {
        $subject = new MockeryMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Configuration::class);

        $mock->shouldReceive('getTestDirectories')->once()->andReturn([]);

        $this->expectException(MockFailureException::class);

        $subject->finalize();
    }

    public function testMockWithGoodPredictions() : void {
        $subject = new MockeryMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Configuration::class);

        $mock->shouldReceive('getTestDirectories')->once()->andReturn([]);

        $mock->getTestDirectories();

        $subject->finalize();

        self::assertSame(1, $subject->getAssertionCount());
    }

}