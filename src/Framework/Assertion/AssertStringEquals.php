<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandSummary;

final class AssertStringEquals extends AbstractAssertion implements Assertion {
    protected function getSummary() : AssertionMessage {
        return new BinaryOperandSummary($this->getExpected(), $this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        return new BinaryOperandDetails($this->getExpected(), $this->getActual());
    }
}