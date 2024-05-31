<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Event;

use Labrador\AsyncEvent\AbstractEvent;
use Labrador\AsyncUnit\Framework\Statistics\ProcessedTestSuiteSummary;

/**
 * @extends AbstractEvent<ProcessedTestSuiteSummary>
 */
final class TestSuiteFinishedEvent extends AbstractEvent {

    public function __construct(ProcessedTestSuiteSummary $target) {
        parent::__construct(Events::TEST_SUITE_FINISHED, $target);
    }

}