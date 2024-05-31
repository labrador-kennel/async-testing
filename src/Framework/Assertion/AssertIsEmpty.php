<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\EmptyUnaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\EmptyUnaryOperandSummary;

final class AssertIsEmpty implements Assertion {

    public function __construct(private mixed $actual) {}

    public function assert() : AssertionResult {
        $factoryMethod = empty($this->actual) ? 'validAssertion' : 'invalidAssertion';
        return AssertionResultFactory::$factoryMethod(
            new EmptyUnaryOperandSummary($this->actual),
            new EmptyUnaryOperandDetails($this->actual)
        );
    }
}