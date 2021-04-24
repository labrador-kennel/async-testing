<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AssertionComparisonDisplay;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

/**
 * @covers \Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertFloatEquals
 */
class AsyncAssertFloatEqualsTest extends AbstractAsyncAssertionTestCase {
    /**
     * @dataProvider nonFloatProvider
     */
    public function testBadTypes($value, string $type) {
        $this->runBadTypeAssertions($value, $type);
    }

    protected function getAssertion($expected, Promise|Generator|Coroutine $actual) : AsyncAssertion {
        return new AsyncAssertFloatEquals($expected, $actual);
    }

    protected function getExpectedValue() : float {
        return 3.14;
    }

    protected function getBadValue() : float {
        return 9.99;
    }

    protected function getExpectedType() : string {
        return 'double';
    }

    protected function getExpectedAssertionComparisonDisplay($expected, $actual) : AssertionComparisonDisplay {
        return new BinaryVarExportAssertionComparisonDisplay($expected, $actual);
    }

}