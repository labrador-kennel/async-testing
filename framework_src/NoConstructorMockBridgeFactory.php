<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\AnnotatedContainer\Autowire\AutowireableFactory;

final class NoConstructorMockBridgeFactory implements MockBridgeFactory {

    public function make(string $mockBridgeClass): MockBridge {
        $mockBridge = new $mockBridgeClass();
        assert($mockBridge instanceof MockBridge);
        return $mockBridge;
    }

}