<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Stub;


use Labrador\AsyncUnit\Framework\Exception\MockFailureException;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridge;

class FailingMockBridgeStub implements MockBridge {

    public function initialize(): void {
        // TODO: Implement initialize() method.
    }

    public function finalize(): void {
        throw new MockFailureException('Thrown from the FailingMockBridgeStub');
    }

    public function createMock(string $type): object {
        return new \stdClass();
    }

    public function getAssertionCount(): int {
        return 0;
    }
}