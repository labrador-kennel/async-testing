<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\FalseUnaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\FalseUnaryOperandSummary;

final class AssertIsFalse extends AbstractAssertion implements Assertion {

    public function __construct(mixed $actual) {
        parent::__construct(false, $actual);
    }

    protected function getSummary() : AssertionMessage {
        return new FalseUnaryOperandSummary($this->getActual());
    }

    protected function getDetails() : AssertionMessage {
        return new FalseUnaryOperandDetails($this->getActual());
    }
}