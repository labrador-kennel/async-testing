<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Event;

use Labrador\AsyncEvent\AbstractEvent;
use Labrador\AsyncUnit\Framework\Statistics\AggregateSummary;

/**
 * @extends AbstractEvent<AggregateSummary>
 */
final class ProcessingStartedEvent extends AbstractEvent {

    public function __construct(AggregateSummary $aggregateSummary) {
        parent::__construct(Events::PROCESSING_STARTED, $aggregateSummary);
    }

}