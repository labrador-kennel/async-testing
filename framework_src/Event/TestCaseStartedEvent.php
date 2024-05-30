<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Statistics\TestCaseSummary;
use Labrador\AsyncEvent\StandardEvent;

final class TestCaseStartedEvent extends StandardEvent {

    public function __construct(TestCaseSummary $target) {
        parent::__construct(Events::TEST_CASE_STARTED, $target);
    }

    public function getTarget() : TestCaseSummary {
        $target = parent::getTarget();
        assert($target instanceof TestCaseSummary);
        return $target;
    }

}