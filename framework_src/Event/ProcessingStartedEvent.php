<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Event;

use Cspray\Labrador\AsyncUnit\Events;
use Cspray\Labrador\AsyncUnit\Statistics\AggregateSummary;
use Labrador\AsyncEvent\StandardEvent;

final class ProcessingStartedEvent extends StandardEvent {

    public function __construct(AggregateSummary $aggregateSummary) {
        parent::__construct(Events::PROCESSING_STARTED, $aggregateSummary);
    }

    public function getTarget() : AggregateSummary {
        $target = parent::getTarget();
        assert($target instanceof AggregateSummary);
        return $target;
    }

}