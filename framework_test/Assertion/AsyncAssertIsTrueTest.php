<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Future;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\TrueUnaryOperandDetails;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\TrueUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertIsTrueTest extends AbstractAsyncAssertionTestCase {

    protected function getAssertion($expected, Future|Generator $actual) : AsyncAssertion {
        return new AsyncAssertIsTrue($actual);
    }

    protected function getExpected() : mixed {
        return null;
    }

    public function getGoodActual() : array {
        return [
            [true]
        ];
    }

    public function getBadActual() : array {
        return [
            [false],
            [1],
            ['this is not true'],
            [new \stdClass()],
            [[]],
            [['a', 'b', 'c']]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return TrueUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return TrueUnaryOperandDetails::class;
    }

}