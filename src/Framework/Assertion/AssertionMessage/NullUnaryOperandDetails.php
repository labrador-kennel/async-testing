<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

final class NullUnaryOperandDetails extends UnaryOperandDetails {

    protected function getExpectedDescriptor() : string {
        return 'null';
    }
}