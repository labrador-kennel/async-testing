<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

final class FalseUnaryOperandDetails extends UnaryOperandDetails {

    protected function getExpectedDescriptor() : string {
        return 'false';
    }
}