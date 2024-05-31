<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\EmptyUnaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\EmptyUnaryOperandSummary;
use Labrador\AsyncUnit\Framework\Assertion\AssertIsEmpty;

class AssertIsEmptyTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIsEmpty($actual);
    }

    protected function getExpected() : mixed {
        return null;
    }

    public static function getGoodActual() : array {
        return [
            [[]],
            [0],
            [null],
            [false],
            ['']
        ];
    }

    public static function getBadActual() : array {
        return [
            [[1, 2, 3, 4]],
            ['a']
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return EmptyUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return EmptyUnaryOperandDetails::class;
    }
}