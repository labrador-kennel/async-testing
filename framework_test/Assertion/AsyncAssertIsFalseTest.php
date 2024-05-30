<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Future;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\FalseAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\FalseUnaryOperandDetails;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\FalseUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertIsFalseTest extends AbstractAsyncAssertionTestCase {

    protected function getAssertion($expected, Future|Generator $actual) : AsyncAssertion {
        return new AsyncAssertIsFalse($actual);
    }

    protected function getExpected() : mixed {
        return null;
    }

    public function getGoodActual() : array {
        return [
            [false]
        ];
    }

    public function getBadActual() : array {
        return [
            [true],
            [null],
            [1],
            [0],
            [''],
            [[]],
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