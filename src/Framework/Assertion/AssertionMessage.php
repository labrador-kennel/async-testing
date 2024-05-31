<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

/**
 * Represents some information about the results of an Assertion or AsyncAssertion.
 *
 * @package Labrador\AsyncUnit\Framework
 */
interface AssertionMessage {

    /**
     * Provide information for when the Assertion or AsyncAssertion has been processed without the not() modifier.
     *
     * @return string
     */
    public function toString() : string;

    /**
     * Provide information for when the Assertion or AsyncAssertion has been processed with the not() modifier.
     *
     * @return string
     */
    public function toNotString() : string;

}