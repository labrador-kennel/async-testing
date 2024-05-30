<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Future;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\NullUnaryOperandDetails;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\NullUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertIsNullTest extends AbstractAsyncAssertionTestCase {

    protected function getAssertion($expected, Future|Generator $actual) : AsyncAssertion {
        return new AsyncAssertIsNull($actual);
    }

    protected function getExpected() : mixed {
        return null;
    }

    public function getGoodActual() : array {
        return [
            [null]
        ];
    }

    public function getBadActual() : array {
        return [
            ['not null'],
            [0],
            [false],
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