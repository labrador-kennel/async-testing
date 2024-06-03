<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;

final class EmptyUnaryOperandDetails extends UnaryOperandDetails implements AssertionMessage {

    protected function getExpectedDescriptor() : string {
        return 'empty';
    }
}