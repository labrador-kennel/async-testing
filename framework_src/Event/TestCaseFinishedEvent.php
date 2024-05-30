<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Statistics\ProcessedTestCaseSummary;
use Labrador\AsyncEvent\StandardEvent;

final class TestCaseFinishedEvent extends StandardEvent {

    public function __construct(ProcessedTestCaseSummary $testCaseSummary) {
        parent::__construct(Events::TEST_CASE_FINISHED, $testCaseSummary);
    }

    public function getTarget() : ProcessedTestCaseSummary {
        $target = parent::getTarget();
        assert($target instanceof ProcessedTestCaseSummary);
        return $target;
    }

}