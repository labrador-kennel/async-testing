<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Stub;

use Cspray\Labrador\AsyncUnit\MockBridge;
use stdClass;

class MockBridgeStub implements MockBridge {

    private array $calls = [];

    public function initialize(): void {
        $this->calls[] = __FUNCTION__;
    }

    public function createMock(string $class): object {
        $this->calls[] = __FUNCTION__ . ' ' . $class;
        $object = new stdClass();
        $object->class = $class;
        return $object;
    }

    public function getAssertionCount(): int {
        return count($this->calls);
    }

    public function finalize(): void {
        $this->calls[] = __FUNCTION__;
    }

    public function getCalls() : array {
        return $this->calls;
    }
}