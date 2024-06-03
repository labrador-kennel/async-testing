<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\TrueUnaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\TrueUnaryOperandSummary;

final class AssertIsTrue extends AbstractAssertion implements Assertion {
    public function __construct(mixed $actual) {
        parent::__construct(true, $actual);
    }

    protected function getSummary() : AssertionMessage {
        return new TrueUnaryOperandSummary($this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        return new TrueUnaryOperandDetails($this->getActual());
    }
}