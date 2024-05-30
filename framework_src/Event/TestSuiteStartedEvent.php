<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Statistics\TestSuiteSummary;
use Labrador\AsyncEvent\StandardEvent;

final class TestSuiteStartedEvent extends StandardEvent {

    public function __construct(TestSuiteSummary $testSuiteSummary) {
        parent::__construct(Events::TEST_SUITE_STARTED, $testSuiteSummary);
    }

    public function getTarget() : TestSuiteSummary {
        $target = parent::getTarget();
        assert($target instanceof TestSuiteSummary);
        return $target;
    }

}