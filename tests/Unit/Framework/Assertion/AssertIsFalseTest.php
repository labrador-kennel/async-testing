<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\FalseUnaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\FalseUnaryOperandSummary;
use Labrador\AsyncUnit\Framework\Assertion\AssertIsFalse;

class AssertIsFalseTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIsFalse($actual);
    }

    protected function getExpected() : mixed {
        return null;
    }

    public static function getGoodActual() : array {
        return [
            [false]
        ];
    }

    public static function getBadActual() : array {
        return [
            [true],
            [1],
            [0],
            [[]],
            ['not false'],
            [''],
            [new \stdClass()]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return FalseUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return FalseUnaryOperandDetails::class;
    }
}