<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Context;

use Countable;
use Labrador\AsyncUnit\Framework\Assertion\AssertArrayEquals;
use Labrador\AsyncUnit\Framework\Assertion\AssertCountEquals;
use Labrador\AsyncUnit\Framework\Assertion\AssertFloatEquals;
use Labrador\AsyncUnit\Framework\Assertion\AssertInstanceOf;
use Labrador\AsyncUnit\Framework\Assertion\AssertIntEquals;
use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionResult;
use Labrador\AsyncUnit\Framework\Assertion\AssertIsEmpty;
use Labrador\AsyncUnit\Framework\Assertion\AssertIsFalse;
use Labrador\AsyncUnit\Framework\Assertion\AssertIsNull;
use Labrador\AsyncUnit\Framework\Assertion\AssertIsTrue;
use Labrador\AsyncUnit\Framework\Assertion\AssertStringEquals;
use Labrador\AsyncUnit\Framework\Exception\AssertionFailedException;

/**
 * Represents an object created for every #[Test] that provides access to the Assertion API as well as the mechanism for
 * which the TestSuiteRunner verifies the appropriate number of Assertion have taken place.
 *
 * You should not be instantiating this object directly. Instead you should be accessing it from the TestCase::assert
 * method.
 */
final class AssertionContext {

    private function __construct(private CustomAssertionContext $customAssertionContext) {}

    public function addToAssertionCount(int $assertionCount) : void {
        $this->count += $assertionCount;
    }

    public function arrayEquals(array $expected, array $actual, string $message = null) : void {
        $this->doAssertion(new AssertArrayEquals($expected, $actual), $message);
    }

    public function floatEquals(float $expected, float $actual, string $message = null) : void {
        $this->doAssertion(new AssertFloatEquals($expected, $actual), $message);
    }

    public function intEquals(int $expected, int $actual, string $message = null) : void {
        $this->doAssertion(new AssertIntEquals($expected, $actual), $message);
    }

    public function stringEquals(string $expected, string $actual, string $message = null) : void {
        $this->doAssertion(new AssertStringEquals($expected, $actual), $message);
    }

    public function countEquals(int $expected, array|Countable $actual, string $message = null) : void {
        $this->doAssertion(new AssertCountEquals($expected, $actual), $message);
    }

    public function instanceOf(string|object $expected, object $actual, string $message = null) : void {
        $this->doAssertion(new AssertInstanceOf($expected, $actual), $message);
    }

    public function isEmpty(mixed $actual, string $message = null) : void {
        $this->doAssertion(new AssertIsEmpty($actual), $message);
    }

    public function isTrue(bool $actual, string $message = null) : void {
        $this->doAssertion(new AssertIsTrue($actual), $message);
    }

    public function isFalse(bool $actual, string $message = null) : void {
        $this->doAssertion(new AssertIsFalse($actual), $message);
    }

    public function isNull(mixed $actual, string $message = null) : void {
        $this->doAssertion(new AssertIsNull($actual), $message);
    }

    public function __call(string $methodName, array $args) : void {
        $this->doAssertion(
            $this->customAssertionContext->createAssertion($methodName, ...$args),
            null
        );
    }

    private function doAssertion(Assertion $assertion, ?string $message) : void {
        $isNot = $this->isNot;
        $this->invokedAssertionContext();

        $results = $assertion->assert();

        $this->handleAssertionResults($results, $isNot, $message);
    }

    private int $count = 0;

    private bool $isNot = false;

    public function getAssertionCount() : int {
        return $this->count;
    }

    public function not() : self {
        $this->isNot = true;
        return $this;
    }

    private function getDefaultFailureMessage(string $assertionString) : string {
        return sprintf("Failed %s", $assertionString);
    }

    private function invokedAssertionContext() : void {
        $this->count++;
        $this->isNot = false;
    }

    private function handleAssertionResults(AssertionResult $result, bool $isNot, ?string $customMessage) {
        if (($isNot && $result->isSuccessful()) || (!$isNot && !$result->isSuccessful())) {
            throw new AssertionFailedException(
                $customMessage ?? $this->getDefaultFailureMessage($isNot ? $result->getSummary()->toNotString() : $result->getSummary()->toString()),
                $this->getDefaultFailureMessage($isNot ? $result->getDetails()->toNotString() : $result->getDetails()->toString()),
            );
        }
    }

}