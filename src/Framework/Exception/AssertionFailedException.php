<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Exception;

final class AssertionFailedException extends TestFailedException {

    public function __construct(
        string $summary,
        private string $detailedMessage,
    ) {
        parent::__construct($summary);
    }

    public function getDetailedMessage() : string {
        return $this->detailedMessage;
    }

}
