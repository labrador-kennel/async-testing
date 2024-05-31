<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Event;

use Labrador\AsyncEvent\AbstractEvent;
use Labrador\AsyncUnit\Framework\Statistics\TestCaseSummary;

/**
 * @extends AbstractEvent<TestCaseSummary>
 */
final class TestCaseStartedEvent extends AbstractEvent {

    public function __construct(TestCaseSummary $target) {
        parent::__construct(Events::TEST_CASE_STARTED, $target);
    }

}
