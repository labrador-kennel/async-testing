<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Generator;
use function Amp\async;

abstract class AbstractAsyncAssertion implements AsyncAssertion {

    public function __construct(private readonly Future|Generator $actual) {}

    final public function assert() : Future {
        return async(function() {
            if ($this->actual instanceof Future) {
                $actual = $this->actual->await();
            } else {
                $actual = async(fn() => yield $this->actual)->await();
            }
            return $this->getAssertion($actual)->assert();
        });
    }

    abstract protected function getAssertion(mixed $resolvedActual) : Assertion;
}