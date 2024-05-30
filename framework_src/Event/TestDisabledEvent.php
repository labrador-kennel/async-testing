<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\TestResult;
use Labrador\AsyncEvent\StandardEvent;

final class TestDisabledEvent extends StandardEvent {

    public function __construct(TestResult $testResult) {
        parent::__construct(Events::TEST_DISABLED, $testResult);
    }

    public function getTarget() : TestResult {
        $target = parent::getTarget();
        assert($target instanceof TestResult);
        return $target;
    }

}