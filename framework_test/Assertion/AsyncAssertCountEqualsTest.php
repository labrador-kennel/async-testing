<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Cspray\Labrador\AsyncUnit\Stub\CountableStub;
use Generator;

class AsyncAssertCountEqualsTest extends AbstractAsyncAssertionTestCase {

    protected function getAssertion(mixed $expected, Future|Generator $actual) : AsyncAssertion {
        return new AsyncAssertCountEquals($expected, $actual);
    }

    protected function getExpected() : int {
        return 5;
    }

    public function getGoodActual() : array {
        return [
            [[1, 2, 3, 4, 5]],
            [['a', 'b', 'c', 'd', 'e']],
            [new CountableStub(5)]
        ];
    }

    public function getBadActual() : array {
        return [
            [[]],
            [[1, 2, 3, 4]],
            [[1, 2, 3, 4, 5, 6]],
            [new CountableStub(4)]
        ];
    }

    protected function getSummaryAssertionMessageClass() : string {
        return Assertion\AssertionMessage\CountEqualsMessage::class;
    }

    protected function getDetailsAssertionMessageClass() : string {
        return Assertion\AssertionMessage\CountEqualsMessage::class;
    }
}