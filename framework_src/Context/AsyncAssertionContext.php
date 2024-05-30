<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertArrayEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertCountEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertFloatEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertInstanceOf;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIntEquals;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsEmpty;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsFalse;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsNull;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertIsTrue;
use Cspray\Labrador\AsyncUnit\Assertion\AsyncAssertStringEquals;
use Generator;
use function Amp\async;

final class AsyncAssertionContext {

    use LastAssertionCalledTrait;
    use SharedAssertionContextTrait;

    private function __construct(private readonly CustomAssertionContext $customAssertionContext) {}

    public function arrayEquals(array $expected, Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $results = (new AsyncAssertArrayEquals($expected, $actual))->assert()->await();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function floatEquals(float $expected, Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $results = (new AsyncAssertFloatEquals($expected, $actual))->assert()->await();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function intEquals(int $expected, Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $results = (new AsyncAssertIntEquals($expected, $actual))->assert()->await();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    /**
     * Compare that an $actual string resolved from a promisor is equal to $expected.
     *
     * @param string $expected
     * @param Future<string>|Generator<string> $actual
     * @param string|null $message
     */
    public function stringEquals(string $expected, Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();
        $results = (new AsyncAssertStringEquals($expected, $actual))->assert()->await();
        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function countEquals(int $expected, Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();
        $results = (new AsyncAssertCountEquals($expected, $actual))->assert()->await();
        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function instanceOf(string $expected, Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();
        $results = (new AsyncAssertInstanceOf($expected, $actual))->assert()->await();
        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isTrue(Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();
        $results = (new AsyncAssertIsTrue($actual))->assert()->await();
        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isFalse(Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();
        $results = (new AsyncAssertIsFalse($actual))->assert()->await();
        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isNull(Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();
        $results = (new AsyncAssertIsNull($actual))->assert()->await();
        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function isEmpty(Future|Generator $actual, string $message = null) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();
        $results = (new AsyncAssertIsEmpty($actual))->assert()->await();
        $this->handleAssertionResults($results, $isNot, $message);
    }

    public function __call(string $methodName, array $args) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();
        $results = $this->customAssertionContext->createAsyncAssertion($methodName, ...$args)->assert()->await();
        $this->handleAssertionResults($results, $isNot, null);
    }
}