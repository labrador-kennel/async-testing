<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\MockBridge;

final class NoConstructorMockBridgeFactory implements MockBridgeFactory {

    public function make(string $mockBridgeClass): MockBridge {
        $mockBridge = new $mockBridgeClass();
        assert($mockBridge instanceof MockBridge);
        return $mockBridge;
    }

}