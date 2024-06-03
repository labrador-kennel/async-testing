<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\AssertFloatEquals;
use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandSummary;

class AssertFloatEqualsTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertFloatEquals($expected, $actual);
    }

    protected function getExpected() : float {
        return 9876.54;
    }

    public static function getGoodActual() : array {
        return [
            [9876.54]
        ];
    }

    public static function getBadActual() : array {
        return [
            [1234.56],
            [9876],
            [false],
            [null],
            [[]],
            ['this is not a float'],
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