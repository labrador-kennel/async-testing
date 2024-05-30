<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Future;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\BinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\InvalidTypeBinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertFloatEqualsTest extends AbstractAsyncAssertionTestCase {

    protected function getAssertion($expected, Future|Generator $actual) : AsyncAssertion {
        return new AsyncAssertFloatEquals($expected, $actual);
    }

    protected function getExpected() : float {
        return 3.14;
    }

    public function getGoodActual() : array {
        return [
            [3.14]
        ];
    }

    public function getBadActual() : array {
        return [
            [9.99],
            [1],
            [0],
            [null],
            [true],
            [[]],
            [[3.14]],
            [new \stdClass()]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return BinaryOperandSummary::class;
    }
}