<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\BinaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

class AsyncAssertArrayEqualsTest extends AbstractAsyncAssertionTestCase {

    protected function getAssertion($expected, Future|Generator $actual) : AsyncAssertion {
        return new AsyncAssertArrayEquals($expected, $actual);
    }

    protected function getExpected() : array {
        return ['generators', 'promises', 'coroutines'];
    }

    public function getGoodActual() : array {
        return [
            [['generators', 'promises', 'coroutines']]
        ];
    }

    public function getBadActual() : array {
        return [
            [['blocks', 'io', 'nooooo']],
            [[]],
            [1],
            [0],
            [null],
            [true],
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