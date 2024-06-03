<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\MockBridge;

use Labrador\AsyncUnit\Framework\Configuration\Configuration;
use Labrador\AsyncUnit\Framework\Exception\MockFailureException;
use Labrador\AsyncUnit\Framework\MockBridge\ProphecyMockBridge;
use PHPUnit\Framework\TestCase;

class ProphecyMockBridgeTest extends TestCase {

    public function testMockWithBadPredictions() {
        $subject = new ProphecyMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Configuration::class);

        $mock->getTestDirectories()->shouldBeCalled()->willReturn([]);

        $this->expectException(MockFailureException::class);

        $subject->finalize();
    }

    public function testMockWithGoodPredictions() {
        $subject = new ProphecyMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Configuration::class);

        $mock->getTestDirectories()->shouldBeCalled()->willReturn([]);

        $mock->reveal()->getTestDirectories();

        $subject->finalize();

        self::assertSame(1, $subject->getAssertionCount());
    }

    public function testMockAssertionCount() {
        $subject = new ProphecyMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Configuration::class);

        $mock->getTestDirectories()->shouldBeCalled()->willReturn([]);

        $secondMock = $subject->createMock(Configuration::class);
        $secondMock->getTestDirectories()->shouldBeCalled()->willReturn([]);

        $this->assertSame(2, $subject->getAssertionCount());
    }

}