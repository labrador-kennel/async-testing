<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\MockBridge;

interface MockBridgeFactory {

    public function make(string $mockBridgeClass) : MockBridge;

}