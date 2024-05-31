<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

use Labrador\AsyncUnit\Framework\Exception\AssertionFailedException;
use Labrador\AsyncUnit\Framework\Exception\InvalidStateException;
use Labrador\AsyncUnit\Framework\Exception\TestDisabledException;
use Labrador\AsyncUnit\Framework\Exception\TestErrorException;
use Labrador\AsyncUnit\Framework\Exception\TestFailedException;
use SebastianBergmann\Timer\Duration;

/**
 * A type that is responsible for conveying the details about the processing of a specific test.
 *
 * @package Labrador\AsyncUnit\Framework
 */
interface TestResult {

    /**
     * The TestCase that was created for the given test; please keep in mind that each test has its own TestCase created
     * and this object is unique per TestResult.
     *
     * @return TestCase
     */
    public function getTestCase() : TestCase;

    public function getTestMethod() : string;

    public function getDataSetLabel() : ?string;

    public function getState() : TestState;

    public function getDuration() : Duration;

    public function getException() : TestFailedException|AssertionFailedException|TestDisabledException|TestErrorException|null;


}