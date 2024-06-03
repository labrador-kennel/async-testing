<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

final class AssertionResultFactory {

    private function __construct() {}

    public static function validAssertion(AssertionMessage $summary, AssertionMessage $details) : AssertionResult {
        return new class($summary, $details) implements AssertionResult {

            public function __construct(private AssertionMessage $summary, private AssertionMessage $details) {}

            public function isSuccessful() : bool {
                return true;
            }

            public function getSummary() : AssertionMessage {
                return $this->summary;
            }

            public function getDetails() : AssertionMessage {
                return $this->details;
            }
        };
    }

    public static function invalidAssertion(AssertionMessage $summary, AssertionMessage $details) : AssertionResult {
        return new class($summary, $details) implements AssertionResult {

            public function __construct(
                private AssertionMessage $summary,
                private AssertionMessage $details
            ) {}

            public function isSuccessful() : bool {
                return false;
            }

            public function getSummary() : AssertionMessage {
                return $this->summary;
            }

            public function getDetails() : AssertionMessage {
                return $this->details;
            }
        };
    }

}

