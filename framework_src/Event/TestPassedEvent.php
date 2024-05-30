<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\TestResult;
use Labrador\AsyncEvent\StandardEvent;

final class TestPassedEvent extends StandardEvent {

    public function __construct(TestResult $target, array $data = []) {
        parent::__construct(Events::TEST_PASSED, $target, $data);
    }

    public function getTarget() : TestResult {
        return parent::getTarget();
    }
}