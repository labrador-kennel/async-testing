<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Event;

use Labrador\AsyncEvent\AbstractEvent;
use Labrador\AsyncUnit\Framework\Statistics\TestSuiteSummary;

/**
 * @extends AbstractEvent<TestSuiteSummary>
 */
final class TestSuiteStartedEvent extends AbstractEvent {

    public function __construct(TestSuiteSummary $testSuiteSummary) {
        parent::__construct(Events::TEST_SUITE_STARTED, $testSuiteSummary);
    }

}