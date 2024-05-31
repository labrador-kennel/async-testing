<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

use Labrador\AsyncUnit\Framework\Assertion\AssertionContext;
use Labrador\AsyncUnit\Framework\Context\ExpectationContext;
use Labrador\AsyncUnit\Framework\Context\TestExpector;
use Labrador\AsyncUnit\Framework\Exception\InvalidStateException;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridge;

/**
 * Represents a type that acts a collection of #[Test] methods to be ran as well as the code necessary to support
 * running each #[Test].
 *
 * The TestCase is an abstract type as opposed to an interface as there are specific concrete functionalities that are
 * expected to be provided by a TestCase to ensure proper running of a test suite.
 */
abstract class TestCase {

    /**
     * A private constructor to ensure that the TestSuiteRunner has complete control over the invocation of a TestCase
     * object creation.
     *
     * Due to the nature of the functionality exposed by this library there are aspects of running a TestCase that need
     * hard, concrete implementation details that do not adhere well to the concept of an interface. This TestCase is
     * intentionally designed to lockdown the internal functionality required by the specification of the framework
     * while keeping open things that are useful in the context of writing unit tests.
     */
    final public function __construct(
        public readonly TestSuite $testSuite,
        protected readonly AssertionContext $assert,
        private ExpectationContext $expectationContext,
        private ?MockBridge $testMocker = null
    ) {}

    final public function getAssertionCount() : int {
        return $this->assert->getAssertionCount();
    }

    final protected function expect() : TestExpector {
        return $this->expectationContext;
    }

    final public function mocks() : MockBridge {
        if (is_null($this->testMocker)) {
            $msg = 'Attempted to create a mock but no MockBridge was defined. Please ensure you\'ve configured a mockBridge in your configuration.';
            throw new InvalidStateException($msg);
        }

        return $this->testMocker;
    }

}
