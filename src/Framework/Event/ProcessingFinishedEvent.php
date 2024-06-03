<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Event;

use Labrador\AsyncEvent\AbstractEvent;
use Labrador\AsyncUnit\Framework\Statistics\ProcessedAggregateSummary;

/**
 * @extends AbstractEvent<ProcessedAggregateSummary>
 */
final class ProcessingFinishedEvent extends AbstractEvent {

    public function __construct(ProcessedAggregateSummary $summary) {
        parent::__construct(Events::PROCESSING_FINISHED, $summary);
    }

}
