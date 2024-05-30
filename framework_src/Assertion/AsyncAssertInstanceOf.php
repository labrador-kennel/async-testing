<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Coroutine;
use Amp\Future;
use Amp\Promise;
use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Generator;

final class AsyncAssertInstanceOf extends AbstractAsyncAssertion {

    public function __construct(private string|object $expected, Future|Generator $actual) {
        if (is_string($expected) && !class_exists($expected) && !interface_exists($expected)) {
            $msg = sprintf(
                'The expected value must be a valid class but %s was given',
                var_export($expected, true)
            );
            throw new InvalidArgumentException($msg);
        }
        parent::__construct($actual);
    }

    protected function getAssertion(mixed $resolvedActual) : Assertion {
        return new AssertInstanceOf($this->expected, $resolvedActual);
    }
}