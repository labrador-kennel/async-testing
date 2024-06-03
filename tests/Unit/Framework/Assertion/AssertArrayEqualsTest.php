<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\AssertArrayEquals;
use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandSummary;
use stdClass;

class AssertArrayEqualsTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertArrayEquals($expected, $actual);
    }


    protected function getExpected() : array {
        return ['a', 'b', 'c'];
    }

    public static function getGoodActual() : array {
        return [
            [['a', 'b', 'c']]
        ];
    }

    public static function getBadActual() : array {
        return [
            [['z', 'x', 'y']],
            [1],
            [[]],
            [true],
            [null],
            [new stdClass()]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }

}