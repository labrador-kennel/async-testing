<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\MockBridge;


use Amp\Success;
use Cspray\Labrador\Application;
use Cspray\Labrador\AsyncUnit\Exception\MockFailureException;
use PHPUnit\Framework\TestCase;

class MockeryMockBridgeTest extends TestCase {

    public function testMockWithBadPredictions() : void {
        $this->markTestSkipped('Need to reimplement Application replacement');
        $subject = new MockeryMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Application::class);

        $mock->expects()->start()->andReturn(new Success());

        $this->expectException(MockFailureException::class);

        $subject->finalize();
    }

    public function testMockWithGoodPredictions() : void {
        $this->markTestSkipped('Need to reimplement Application replacement');
        $this->expectNotToPerformAssertions();
        $subject = new MockeryMockBridge();

        $subject->initialize();
        $mock = $subject->createMock(Application::class);
        $mock->expects()->start()->andReturn(new Success());

        $mock->start();

        $subject->finalize();
    }

}