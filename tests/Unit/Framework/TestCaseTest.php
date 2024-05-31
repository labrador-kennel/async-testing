<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Assertion\AssertionResult;
use Labrador\AsyncUnit\Framework\Context\AssertionContext;
use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Framework\Context\ExpectationContext;
use Labrador\AsyncUnit\Framework\Exception\AssertionFailedException;
use Labrador\AsyncUnit\Framework\Exception\InvalidStateException;
use Labrador\AsyncUnit\Framework\ImplicitTestSuite;
use Labrador\AsyncUnit\Framework\MockBridge\MockBridge;
use Labrador\AsyncUnit\Framework\Model\TestModel;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\AssertNotTestCase;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\CustomAssertionTestCase;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\FailingTestCase;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\MockAwareTestCase;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\MockBridgeStub;
use Psr\Log\LoggerInterface;
use function Amp\async;

class TestCaseTest extends \PHPUnit\Framework\TestCase {

    public function testFailingAssertionHasCustomMessage() {
        [$subject] = $this->getSubjectAndContexts(FailingTestCase::class);
        $assertionException = null;
        try {
            $subject->doFailureWithCustomMessage();
        } catch (AssertionFailedException $exception) {
            $assertionException = $exception;
        } finally {
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

    public function testRunningCustomAssertions() {
        /** @var CustomAssertionTestCase $subject */
        /** @var AssertionContext $assertionContext */
        /** @var CustomAssertionContext $customAssertionContext */
        [$subject, $assertionContext, $customAssertionContext] = $this->getSubjectAndContexts(CustomAssertionTestCase::class);

        $assertion = $this->getMockBuilder(Assertion::class)->getMock();
        $assertResult = $this->getMockBuilder(AssertionResult::class)->getMock();
        $assertResult->expects($this->once())->method('isSuccessful')->willReturn(true);
        $assertion->expects($this->once())->method('assert')->willReturn($assertResult);
        $state = new \stdClass();
        $state->args = null;
        $customAssertionContext->registerAssertion('myCustomAssertion', function(...$args) use($assertion, $state) {
            $state->args = $args;
            return $assertion;
        });

        $subject->doCustomAssertion();

        $this->assertSame(1, $assertionContext->getAssertionCount());
        $this->assertSame([1,2,3], $state->args);
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

    public function getSubjectAndContexts(string $testCase, MockBridge $mockBridge = null) {
        $customAssertionContext = (new \ReflectionClass(CustomAssertionContext::class))->newInstanceWithoutConstructor();
        $reflectedAssertionContext = new \ReflectionClass(AssertionContext::class);
        $assertionContext = $reflectedAssertionContext->newInstanceWithoutConstructor();
        $assertionContextConstructor = $reflectedAssertionContext->getConstructor();
        $assertionContextConstructor->invoke($assertionContext, $customAssertionContext);

        $reflectedExpectationContext = new \ReflectionClass(ExpectationContext::class);
        $fakeTestModel = new TestModel('SomeClass', 'someMethod');
        $expectationContext = $reflectedExpectationContext->newInstanceWithoutConstructor();
        $expectationContextConstructor = $reflectedExpectationContext->getConstructor();
        $expectationContextConstructor->invoke($expectationContext, $fakeTestModel, $assertionContext, $mockBridge);

        $reflectedSubject = new \ReflectionClass($testCase);
        $constructor = $reflectedSubject->getConstructor();
        $subject = $reflectedSubject->newInstanceWithoutConstructor();
        $constructor->invoke(
            $subject,
            (new \ReflectionClass(ImplicitTestSuite::class))->newInstanceWithoutConstructor(),
            $assertionContext,
            $expectationContext,
            $mockBridge
        );

        return [$subject, $assertionContext, $customAssertionContext, $expectationContext];
    }
}