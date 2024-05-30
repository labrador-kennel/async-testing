<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Future;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\EmptyUnaryOperandDetails;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\EmptyUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;
use PHPUnit\Framework\TestCase;

class AsyncAssertIsEmptyTest extends AbstractAsyncAssertionTestCase {

    protected function getAssertion(mixed $expected, Future|Generator $actual) : AsyncAssertion {
        return new AsyncAssertIsEmpty($actual);
    }

    protected function getExpected() : mixed {
        return null;
    }

    public function getGoodActual() : array {
        return [
            [[]],
            [false],
            [''],
            [null]
        ];
    }

    public function getBadActual() : array {
        return [
            [[1,2,3]],
            [1],
            [true],
            ['some string']
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return EmptyUnaryOperandSummary::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return EmptyUnaryOperandDetails::class;
    }
}