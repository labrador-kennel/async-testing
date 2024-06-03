<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Event;

use Labrador\AsyncEvent\AbstractEvent;
use Labrador\AsyncUnit\Framework\TestResult;

/**
 * @extends AbstractEvent<TestResult>
 */
final class TestErroredEvent extends AbstractEvent {

    public function __construct(TestResult $target) {
        parent::__construct(Events::TEST_ERRORED, $target);
    }

}