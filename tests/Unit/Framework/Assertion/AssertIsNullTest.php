<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\NullUnaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\NullUnaryOperandSummary;
use Labrador\AsyncUnit\Framework\Assertion\AssertIsNull;

class AssertIsNullTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIsNull($actual);
    }

    public static function getGoodActual() : array {
        return [
            [null]
        ];
    }

    protected function getExpected() : mixed {
        return null;
    }

    public static function getBadActual() : array {
        return [
            ['not null'],
            [1],
            [false],
            [0],
            [[]]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return NullUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return NullUnaryOperandDetails::class;
    }
}