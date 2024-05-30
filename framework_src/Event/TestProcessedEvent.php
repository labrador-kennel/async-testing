<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\TestResult;
use Labrador\AsyncEvent\StandardEvent;

final class TestProcessedEvent extends StandardEvent {

    public function __construct(TestResult $target, array $data = []) {
        parent::__construct(Events::TEST_PROCESSED, $target, $data);
    }

    public function getTarget() : TestResult {
        $target = parent::getTarget();
        assert($target instanceof TestResult);
        return $target;
    }

}