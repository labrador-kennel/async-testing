<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\AssertIntEquals;
use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandSummary;

class AssertIntEqualsTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIntEquals($expected, $actual);
    }

    protected function getExpected() : int {
        return 1234;
    }

    public static function getGoodActual() : array {
        return [
            [1234]
        ];
    }

    public static function getBadActual() : array {
        return [
            [9876],
            [1234.56],
            [[]],
            ['not an int'],
            [new \stdClass()],
            [null],
            [true]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return BinaryOperandDetails::class;
    }
}