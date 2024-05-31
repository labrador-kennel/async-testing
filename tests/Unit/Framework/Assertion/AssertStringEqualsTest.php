<?php

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandSummary;
use Labrador\AsyncUnit\Framework\Assertion\AssertStringEquals;

class AssertStringEqualsTest extends AbstractAssertionTestCase {

    protected function getAssertion($value, $actual) : Assertion {
        return new AssertStringEquals($value, $actual);
    }

    public function getGoodActual() : array {
        return [
            ['async unit']
        ];
    }

    protected function getExpected() : string {
        return 'async unit';
    }

    public function getBadActual() : array {
        return [
            ['blocking code'],
            ['phpunit'],
            [1],
            [1.23],
            [null],
            [true],
            [['async unit']],
            [new \stdClass()]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return BinaryOperandDetails::class;
    }

}
