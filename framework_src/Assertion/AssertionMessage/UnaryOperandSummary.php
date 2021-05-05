<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use Cspray\Labrador\AsyncUnit\AssertionMessage;

abstract class UnaryOperandSummary implements AssertionMessage {

    public function __construct(private mixed $actual) {}

    public function toString() : string {
        return sprintf('asserting type "%s" is %s', strtolower(gettype($this->actual)), $this->getExpectedDescriptor());
    }

    public function toNotString() : string {
        return sprintf('asserting type "%s" is not %s', strtolower(gettype($this->actual)), $this->getExpectedDescriptor());
    }

    abstract protected function getExpectedDescriptor() : string;

}