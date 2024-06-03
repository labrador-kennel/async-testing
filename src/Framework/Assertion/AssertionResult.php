<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

/**
 * Represents the data about a call to {@see Assertion::assert} or {@see AsyncAssertion::assert} that could be a
 * successful assertion or a failure.
 */
interface AssertionResult {

    /**
     * Whether or not the Assertion passed without error.
     *
     * @return bool
     */
    public function isSuccessful() : bool;

    /**
     * A brief, 1-line summary of the Assertion that was carried out.
     *
     * @return AssertionMessage
     */
    public function getSummary() : AssertionMessage;

    /**
     * An exhaustive, descriptive, potentially multi-line detail of the Assertion that was carried out.
     *
     * This is the information that should be displayed to the user when an Assertion has failed and it is important that
     * it has appropriate information and details in it to help in fixing the test to pass.
     *
     * @return AssertionMessage
     */
    public function getDetails() : AssertionMessage;

}