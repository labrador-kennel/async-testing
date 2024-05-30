<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Future;

/**
 * A type that represents an assertion in a #[Test] that is expected to be asynchronous in nature.
 *
 * An AsyncAssertion is expected to perform some asynchronous operation while checking the provided values. As well it
 * is expected that an AsyncAssertion will implicitly resolve any Promise, Generator, or Coroutine that is passed to them.
 * All of the same principles and rules that apply to Assertion also applies to this type, with the exception that this
 * type should handle async as a first-class citizen within tests.
 *
 * @package Cspray\Labrador\AsyncUnit
 */
interface AsyncAssertion {

    /**
     * Resolve a Promise with the AssertionResult when completed checking.
     *
     * You SHOULD NOT throw an Exception from this method to signify that the Assertion failed. An assertion potentially
     * failing is, and should be, expected. We should avoid throwing Exceptions in places that aren't exceptional, and
     * something we expect to happen isn't exceptional. The only Exceptions thrown from this method should be ones that
     * truly represent the Assertion cannot be processed because of something that was not, or should not, be accounted
     * for.
     *
     * @return Future<AssertionResult>
     */
    public function assert() : Future;

}