<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

/**
 * A type that represents an individual assertion in a #[Test] that is expected to be synchronous in nature.
 *
 * An Assertion is expected to work on "resolved" values. That is values that are not reliant on a Promise or Generator
 * or Coroutine completing or running to conclusion. Passing a Promise or Generator or Coroutine to one of these
 * Assertions is HIGHLY indicative of a logical error where you're misapplying which Assertion context to use.
 */
interface Assertion {

    /**
     * Run an assertion on $actual and return an AssertionResult detailing whether the assertion succeeded or failed.
     *
     * You SHOULD NOT throw an Exception from this method to signify that the Assertion failed. An assertion potentially
     * failing is, and should be, expected. We should avoid throwing Exceptions in places that aren't exceptional, and
     * something we expect to happen isn't exceptional. The only Exceptions thrown from this method should be ones that
     * truly represent the Assertion cannot be processed because of something that was not, or should not, be accounted
     * for.
     *
     * @return AssertionResult
     */
    public function assert() : AssertionResult;

}