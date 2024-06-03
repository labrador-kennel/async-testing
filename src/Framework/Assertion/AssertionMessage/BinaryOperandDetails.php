<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

final class BinaryOperandDetails implements AssertionMessage {

    public function __construct(private mixed $expected, private mixed $actual) {}

    public function toString() : string {
        return sprintf(
            'comparing actual value %s equals %s',
            var_export($this->actual, true),
            var_export($this->expected, true)
        );
    }

    public function toNotString() : string {
        return sprintf(
            'comparing actual value %s does not equal %s',
            var_export($this->actual, true),
            var_export($this->expected, true)
        );
    }
}