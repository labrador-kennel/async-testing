<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Statistics\ProcessedTestSuiteSummary;
use Labrador\AsyncEvent\StandardEvent;

final class TestSuiteFinishedEvent extends StandardEvent {

    public function __construct(ProcessedTestSuiteSummary $target) {
        parent::__construct(Events::TEST_SUITE_FINISHED, $target);
    }

    public function getTarget() : ProcessedTestSuiteSummary {
        $target = parent::getTarget();
        assert($target instanceof ProcessedTestSuiteSummary);
        return $target;
    }

}