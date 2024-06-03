<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

abstract class UnaryOperandDetails implements AssertionMessage {

    public function __construct(private mixed $actual) {}

    public function toString() : string {
        $details = var_export($this->actual, true);
        if (is_null($this->actual)) {
            $details = 'null';
        }
        return sprintf(
            'comparing %s is %s',
            $details,
            $this->getExpectedDescriptor()
        );
    }

    public function toNotString() : string {
        $details = var_export($this->actual, true);
        if (is_null($this->actual)) {
            $details = 'null';
        }
        return sprintf(
            'comparing %s is not %s',
            $details,
            $this->getExpectedDescriptor()
        );
    }

    abstract protected function getExpectedDescriptor() : string;
}