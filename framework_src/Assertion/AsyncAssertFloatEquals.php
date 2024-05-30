<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Future;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Generator;

final class AsyncAssertFloatEquals extends AbstractAsyncAssertion {

    public function __construct(private float $expected, Future|Generator $actual) {
        parent::__construct($actual);
    }

    protected function getAssertion(mixed $actual) : Assertion {
        return new AssertFloatEquals($this->expected, $actual);
    }
}