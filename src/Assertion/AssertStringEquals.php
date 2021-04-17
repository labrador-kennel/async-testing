<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncTesting\Assertion;

use Cspray\Labrador\AsyncTesting\Assertion;
use Cspray\Labrador\AsyncTesting\Assertion\AssertionComparisonDisplay\BinaryVarExportAssertionComparisonDisplay;
use Cspray\Labrador\AsyncTesting\AssertionResult;
use Cspray\Labrador\AsyncTesting\AssertionResultFactory;

class AssertStringEquals implements Assertion {

    public function __construct(private string $expected) {}

    public function assert(mixed $actual, string $errorMessage = null) : AssertionResult {
        if (!is_string($actual)) {
            $errorMessage = $errorMessage ?? sprintf('Failed asserting that a value with type "%s" is comparable to type "string".', gettype($actual));
            return AssertionResultFactory::invalidAssertion(
                $errorMessage,
                new BinaryVarExportAssertionComparisonDisplay($this->expected, $actual)
            );
        } else if ($this->expected !== $actual) {
            return AssertionResultFactory::invalidAssertion(
                $errorMessage ?? 'Failed comparing that 2 strings are equal to one another',
                new BinaryVarExportAssertionComparisonDisplay($this->expected, $actual)
            );
        }

        return AssertionResultFactory::validAssertion();
    }
}