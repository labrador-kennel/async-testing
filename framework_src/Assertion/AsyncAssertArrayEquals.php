<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Future;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;

final class AsyncAssertArrayEquals extends AbstractAsyncAssertion implements AsyncAssertion {

    public function __construct(private array $expected, Future|Generator $actual) {
        parent::__construct($actual);
    }

    protected function getAssertion(mixed $resolvedActual) : Assertion {
        return new AssertArrayEquals($this->expected, $resolvedActual);
    }
}