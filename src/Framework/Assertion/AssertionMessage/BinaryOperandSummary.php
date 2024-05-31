<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

final class BinaryOperandSummary implements AssertionMessage {

    public function __construct(private mixed $expected, private mixed $actual) {}

    public function toString() : string {
        return sprintf(
            'asserting type "%s" equals type "%s"',
            strtolower(gettype($this->expected)),
            strtolower(gettype($this->actual))
        );
    }

    public function toNotString() : string {
        return sprintf(
            'asserting type "%s" does not equal type "%s"',
            strtolower(gettype($this->expected)),
            strtolower(gettype($this->actual))
        );
    }
}