<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\TrueUnaryOperandDetails;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\TrueUnaryOperandSummary;
use Labrador\AsyncUnit\Framework\Assertion\AssertIsTrue;

class AssertIsTrueTest extends AbstractAssertionTestCase {

    protected function getAssertion($expected, $actual) : Assertion {
        return new AssertIsTrue($actual);
    }

    public function getGoodActual() : array {
        return [
            [true]
        ];
    }

    protected function getExpected() : mixed {
        return null;
    }

    public function getBadActual() : array {
        return [
            [false],
            [1],
            [0],
            [[1]],
            [new \stdClass()],
            ['this is not true']
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return TrueUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return TrueUnaryOperandDetails::class;
    }
}