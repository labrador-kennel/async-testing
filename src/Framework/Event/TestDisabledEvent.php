<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Event;

use Labrador\AsyncEvent\AbstractEvent;
use Labrador\AsyncUnit\Framework\TestResult;

/**
 * @extends AbstractEvent<TestResult>
 */
final class TestDisabledEvent extends AbstractEvent {

    public function __construct(TestResult $testResult) {
        parent::__construct(Events::TEST_DISABLED, $testResult);
    }
}
