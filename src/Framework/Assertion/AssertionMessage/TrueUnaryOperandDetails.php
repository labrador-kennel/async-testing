<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

final class TrueUnaryOperandDetails extends UnaryOperandDetails {

    protected function getExpectedDescriptor() : string {
        return 'true';
    }
}