<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Event;

use Labrador\AsyncEvent\AbstractEvent;
use Labrador\AsyncUnit\Framework\Statistics\ProcessedTestCaseSummary;

/**
 * @extends AbstractEvent<ProcessedTestCaseSummary>
 */
final class TestCaseFinishedEvent extends AbstractEvent {

    public function __construct(ProcessedTestCaseSummary $testCaseSummary) {
        parent::__construct(Events::TEST_CASE_FINISHED, $testCaseSummary);
    }

}