<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

final class TrueUnaryOperandSummary extends UnaryOperandSummary {

    protected function getExpectedDescriptor() : string {
        return 'true';
    }
}