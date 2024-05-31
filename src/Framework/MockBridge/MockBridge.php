<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\MockBridge;

use Labrador\AsyncUnit\Framework\Exception\MockFailureException;

/**
 * Represents an implementation that knows how to create a mock from a 3rd-party library and facilitate coordinating with
 * the AsyncUnit framework runner to port any 3rd-party specific exceptions or APIs into appropriate AsyncUnit counterparts.
 */
interface MockBridge {

    /**
     * Perform whatever setup functionality that might be required for the 3rd party implementation.
     *
     * This method will be called one time for every test.
     */
    public function initialize() : void;

    public function createMock(string $type) : object;

    /**
     * Perform whatever validation or verification of expected mock calls are required for the given 3rd-party library.
     *
     * @throws MockFailureException
     */
    public function finalize() : void;

    /**
     * Return a value greater than 0 that represents the number of assertions this MockBridge will carry out for ALL of
     * the mocks created by this instance.
     *
     * The precise number returned here is dependent on the underlying mock library being implemented. It is imperative
     * that you return a positive number here, otherwise a test that only makes expectations on a mock will fail due to
     * no assertions being made.
     *
     * @return int
     */
    public function getAssertionCount() : int;

}