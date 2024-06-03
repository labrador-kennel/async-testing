<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\NullUnaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\NullUnaryOperandSummary;

final class AssertIsNull extends AbstractAssertion implements Assertion {
    public function __construct(mixed $actual) {
        parent::__construct(null, $actual);
    }

    protected function getSummary() : AssertionMessage {
        return new NullUnaryOperandSummary($this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        return new NullUnaryOperandDetails($this->getActual());
    }

}