<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Stub;

use Labrador\AsyncUnit\Test\Unit\Framework\MockBridge;
use Labrador\AsyncUnit\Test\Unit\Framework\MockBridgeFactory;

class FailingMockBridgeFactory implements MockBridgeFactory {

    public function make(string $mockBridgeClass): MockBridge {
        return new FailingMockBridgeStub();
    }
}