<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\MockBridge;

use Labrador\AsyncUnit\Framework\Exception\MockFailureException;
use Labrador\AsyncUnit\Framework\Exception\UnsupportedOperationException;
use Prophecy\Exception\Prediction\PredictionException;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;

final class ProphecyMockBridge implements MockBridge {

    private Prophet $prophet;

    public function __construct() {
        if (!class_exists(ObjectProphecy::class)) {
            $msg = 'Unable to use the ProphecyMockBridge without installing phpspec/prophecy.';
            throw new UnsupportedOperationException($msg);
        }
    }

    public function initialize() : void {
        $this->prophet = new Prophet();
    }

    public function finalize() : void {
        try {
            $this->prophet->checkPredictions();
        } catch (PredictionException $exception) {
            throw new MockFailureException($exception->getMessage(), previous: $exception);
        }
    }

    public function createMock(string $class) : ObjectProphecy {
        return $this->prophet->prophesize($class);
    }

    public function getAssertionCount(): int {
        return count($this->prophet->getProphecies());
    }

}