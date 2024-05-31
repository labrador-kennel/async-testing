<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Context;

use Labrador\AsyncUnit\Framework\Assertion\AssertionContext;
use Labrador\AsyncUnit\Framework\Exception\MockFailureException;
use Labrador\AsyncUnit\Framework\Exception\TestErrorException;
use Labrador\AsyncUnit\Framework\Exception\TestFailedException;
use Labrador\AsyncUnit\Framework\Exception\TestOutputException;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridge;
use Labrador\AsyncUnit\Framework\Model\TestModel;
use Throwable;

final class ExpectationContext implements TestExpector {

    private string $actualOutput = '';

    private ?Throwable $thrownException = null;

    private ?string $expectedExceptionClass = null;

    private ?string $expectedExceptionMessage = null;

    private ?int $expectedAssertionCount = null;

    public function __construct(
        private readonly TestModel $testModel,
        private readonly AssertionContext $assertionContext,
        private readonly ?MockBridge $mockBridge
    ) {}

    public function setActualOutput(string $output) : void {
        $this->actualOutput = $output;
    }

    public function setThrownException(Throwable $throwable) : void {
        $this->thrownException = $throwable;
    }

    /**
     * @param class-string<Throwable> $type
     * @return void
     */
    public function exception(string $type) : void {
        $this->expectedExceptionClass = $type;
    }

    public function exceptionMessage(string $message) : void {
        $this->expectedExceptionMessage = $message;
    }

    public function noAssertions() : void {
        $this->expectedAssertionCount = 0;
    }

    public function validateExpectations() : TestFailedException|TestErrorException|TestOutputException|MockFailureException|null {
        return $this->validateThrownException() ??
            $this->validateAssertionCount() ??
            $this->validateOutput() ??
            $this->validateMocks() ??
            null;
    }

    private function validateAssertionCount() : ?TestFailedException {
        // If there is an expected exception we should not assume that assertions were ran
        if (!is_null($this->expectedExceptionClass)) {
            return null;
        }

        if (!is_null($this->expectedAssertionCount)) {
            $totalAssertionCount = $this->assertionContext->getAssertionCount();
            if ($totalAssertionCount !== $this->expectedAssertionCount) {
                $msg = sprintf(
                    'Expected %s::%s to make 0 assertions but made %s',
                    $this->testModel->getClass(),
                    $this->testModel->getMethod(),
                    $totalAssertionCount
                );
                return new TestFailedException($msg);
            }

            return null;
        }

        if ($this->assertionContext->getAssertionCount() === 0) {
            $msg = sprintf(
                'Expected "%s::%s" #[Test] to make at least 1 Assertion but none were made.',
                $this->testModel->getClass(),
                $this->testModel->getMethod()
            );
            return new TestFailedException($msg);
        }

        return null;
    }

    private function validateThrownException() : TestFailedException|TestErrorException|null {
        if (isset($this->thrownException) && is_null($this->expectedExceptionClass)) {
            $msg = sprintf(
                'An unexpected exception of type "%s" with code %s and message "%s" was thrown from #[Test] %s::%s',
                $this->thrownException::class,
                $this->thrownException->getCode(),
                $this->thrownException->getMessage(),
                $this->testModel->getClass(),
                $this->testModel->getMethod()
            );
            return new TestErrorException($msg, previous: $this->thrownException);
        } else if (is_null($this->thrownException) && isset($this->expectedExceptionClass)) {
            $msg = sprintf(
                'Failed asserting that an exception of type %s is thrown',
                $this->expectedExceptionClass
            );
            return new TestFailedException($msg);
        } else if (isset($this->thrownException) && !$this->thrownException instanceof $this->expectedExceptionClass) {
            $msg = sprintf(
                'Failed asserting that thrown exception %s extends expected %s',
                $this->thrownException::class,
                $this->expectedExceptionClass
            );
            return new TestFailedException($msg);
        } else if (isset($this->thrownException) && isset($this->expectedExceptionMessage) && $this->thrownException->getMessage() !== $this->expectedExceptionMessage) {
            $msg = sprintf(
                'Failed asserting that thrown exception message "%s" equals expected "%s"',
                $this->thrownException->getMessage(),
                $this->expectedExceptionMessage
            );
            return new TestFailedException($msg);
        }

        return null;
    }

    private function validateOutput() : ?TestOutputException {
        if (!empty($this->actualOutput)) {
            $msg = sprintf(
                'Test had unexpected output:%s%s"%s"',
                PHP_EOL,
                PHP_EOL,
                $this->actualOutput
            );
            return new TestOutputException($msg);
        }

        return null;
    }

    private function validateMocks() : ?MockFailureException {
        if (!is_null($this->mockBridge)) {
            try {
                $this->mockBridge->finalize();
                return null;
            } catch (MockFailureException $mockFailureException) {
                return $mockFailureException;
            }
        }

        return null;
    }

}