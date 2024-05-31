<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionContext;
use Labrador\AsyncUnit\Framework\Assertion\AssertionResult;
use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Framework\Context\ExpectationContext;
use Labrador\AsyncUnit\Framework\Exception\AssertionFailedException;
use Labrador\AsyncUnit\Framework\Exception\InvalidStateException;
use Labrador\AsyncUnit\Framework\ImplicitTestSuite;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridge;
use Labrador\AsyncUnit\Framework\Model\TestModel;
use Labrador\AsyncUnit\Framework\TestCase;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\AssertNotTestCase;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\CustomAssertionTestCase;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\FailingTestCase;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\MockAwareTestCase;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\MockBridgeStub;
use Psr\Log\LoggerInterface;

class TestCaseTest extends \PHPUnit\Framework\TestCase {

    public function testFailingAssertionHasCustomMessage() {
        [$subject] = $this->getSubjectAndContexts(FailingTestCase::class);
        $assertionException = null;
        try {
            $subject->doFailureWithCustomMessage();
        } catch (AssertionFailedException $exception) {
            $assertionException = $exception;
        } finally {
            self::assertInstanceOf(AssertionFailedException::class, $assertionException);
            $this->assertNotNull($assertionException);
            $this->assertSame('my custom message', $assertionException->getMessage());
        }
    }

    public function testRunningOnlyNotAssertionPassing() {
        /** @var AssertNotTestCase $subject */
        /** @var AssertionContext $assertionContext */
        [$subject, $assertionContext] = $this->getSubjectAndContexts(AssertNotTestCase::class);

        $subject->doNotAssertion();
        $this->assertEquals(1, $assertionContext->getAssertionCount());
    }

    public function testRunningOnlyNotAssertionFailing() {
        /** @var AssertNotTestCase $subject */
        [$subject] = $this->getSubjectAndContexts(AssertNotTestCase::class);

        $assertionException = null;
        try{
            $subject->doFailingNotAssertions();
        } catch (AssertionFailedException $exception) {
            $assertionException = $exception;
        } finally {
            $this->assertNotNull($assertionException);
            $this->assertSame('Failed asserting type "string" does not equal type "string"', $assertionException->getMessage());
        }
    }

    public function testRunningBothNotAndRegularAssertionPassing() {
        /** @var AssertNotTestCase $subject */
        /** @var AssertionContext $assertionContext */
        [$subject, $assertionContext] = $this->getSubjectAndContexts(AssertNotTestCase::class);

        $subject->doBothAssertions();

        $this->assertSame(3, $assertionContext->getAssertionCount());
    }

    public function testCreatingMockWithNoBridge() {
        /** @var MockAwareTestCase $subject */
        [$subject] = $this->getSubjectAndContexts(MockAwareTestCase::class);

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('Attempted to create a mock but no MockBridge was defined. Please ensure you\'ve configured a mockBridge in your configuration.');

        $subject->checkCreatingMock();
    }

    public function testCreatingMockWithBridge() {
        $mockBridge = new MockBridgeStub();
        /** @var MockAwareTestCase $subject */
        [$subject] = $this->getSubjectAndContexts(MockAwareTestCase::class, $mockBridge);

        $subject->checkCreatingMock();

        $this->assertNotNull($subject->getCreatedMock());
        $this->assertSame(LoggerInterface::class, $subject->getCreatedMock()->class);

        // We do not expect initialize or finalize to be called here because that's controlled by the TestSuiteRunner
        $this->assertSame([
            'createMock ' . LoggerInterface::class,
        ], $mockBridge->getCalls());
    }

    /**
     * @param class-string<TestCase> $testCase
     * @param MockBridge|null $mockBridge
     * @return array{0: TestCase, 1: AssertionContext, 2: ExpectationContext}
     */
    public function getSubjectAndContexts(string $testCase, MockBridge $mockBridge = null) : array {
        $testSuite = new ImplicitTestSuite();
        $assertionContext = new AssertionContext();
        $expectationContext = new ExpectationContext(
            new TestModel($testCase, 'someMethod'),
            $assertionContext,
            $mockBridge
        );
        $subject = new $testCase($testSuite, $assertionContext, $expectationContext, $mockBridge);

        return [$subject, $assertionContext, $expectationContext];
    }
}