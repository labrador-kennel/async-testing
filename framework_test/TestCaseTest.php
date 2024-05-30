<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Future;
use Amp\Loop;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Context\AssertionContext;
use Cspray\Labrador\AsyncUnit\Context\AsyncAssertionContext;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\Context\ExpectationContext;
use Cspray\Labrador\AsyncUnit\Exception\AssertionFailedException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidConfigurationException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use Cspray\Labrador\AsyncUnit\Model\TestModel;
use Cspray\Labrador\AsyncUnit\Stub\AssertNotTestCase;
use Cspray\Labrador\AsyncUnit\Stub\CustomAssertionTestCase;
use Cspray\Labrador\AsyncUnit\Stub\FailingTestCase;
use Cspray\Labrador\AsyncUnit\Stub\MockAwareTestCase;
use Cspray\Labrador\AsyncUnit\Stub\MockBridgeStub;
use Psr\Log\LoggerInterface;
use function Amp\async;
use function Amp\call;

class TestCaseTest extends \PHPUnit\Framework\TestCase {

    public function testFailingAssertionHasFileAndLine() {
        [$subject] = $this->getSubjectAndContexts(FailingTestCase::class);
        $assertionException = null;
        try {
            $subject->doFailure();
        } catch (AssertionFailedException $exception) {
            $assertionException = $exception;
        } finally {
            $this->assertNotNull($assertionException);
            $this->assertSame('Failed asserting type "string" equals type "string"', $assertionException->getMessage());
            $this->assertSame(__DIR__ . '/Stub/FailingTestCase.php', $assertionException->getAssertionFailureFile());
            $this->assertSame(11, $assertionException->getAssertionFailureLine());
        }
    }

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
            $this->assertSame(__DIR__ . '/Stub/FailingTestCase.php', $assertionException->getAssertionFailureFile());
            $this->assertSame(19, $assertionException->getAssertionFailureLine());
        }
    }

    public function testFailingAsyncAssertionHasFileAndLine() {
        [$subject] = $this->getSubjectAndContexts(FailingTestCase::class);

        $assertionException = null;
        try {
            async($subject->doAsyncFailure(...))->await();
        } catch (AssertionFailedException $exception) {
            $assertionException = $exception;
        } finally {
            $this->assertNotNull($assertionException);
            $this->assertSame('Failed asserting type "string" equals type "string"', $assertionException->getMessage());
            $this->assertSame(__DIR__ . '/Stub/FailingTestCase.php', $assertionException->getAssertionFailureFile());
            $this->assertSame(15, $assertionException->getAssertionFailureLine());
        }
    }

    public function testRunningOnlyNotAssertionPassing() {
        /** @var AssertNotTestCase $subject */
        /** @var AssertionContext $assertionContext */
        /** @var AsyncAssertionContext $asyncAssertionContext */
        [$subject, $assertionContext, $asyncAssertionContext] = $this->getSubjectAndContexts(AssertNotTestCase::class);

        $subject->doNotAssertion();
        $this->assertEquals(1, $assertionContext->getAssertionCount());

        async($subject->doAsyncNotAssertion(...))->await();

        $this->assertEquals(1, $asyncAssertionContext->getAssertionCount());
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
            $this->assertSame(__DIR__ . '/Stub/AssertNotTestCase.php', $assertionException->getAssertionFailureFile());
            $this->assertSame(15, $assertionException->getAssertionFailureLine());
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
        [$subject, $assertionContext, $_, $customAssertionContext] = $this->getSubjectAndContexts(CustomAssertionTestCase::class);

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

    public function testRunningCustomAsyncAssertions() {
        /** @var CustomAssertionTestCase $subject */
        /** @var AsyncAssertionContext $asyncAssertionContext */
        /** @var CustomAssertionContext $customAssertionContext */
        [$subject, $_, $asyncAssertionContext, $customAssertionContext] = $this->getSubjectAndContexts(CustomAssertionTestCase::class);

        $assertion = $this->getMockBuilder(AsyncAssertion::class)->getMock();
        $assertResult = $this->getMockBuilder(AssertionResult::class)->getMock();
        $assertResult->expects($this->once())->method('isSuccessful')->willReturn(true);
        $assertion->expects($this->once())->method('assert')->willReturn(Future::complete($assertResult));
        $state = new \stdClass();
        $state->args = null;
        $customAssertionContext->registerAsyncAssertion('myCustomAssertion', function(...$args) use($assertion, $state) {
            $state->args = $args;
            return $assertion;
        });

        $subject->doCustomAsyncAssertion();

        $this->assertSame(1, $asyncAssertionContext->getAssertionCount());
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

        $reflectedAsyncAssertionContext = new \ReflectionClass(AsyncAssertionContext::class);
        $asyncAssertionContext = $reflectedAsyncAssertionContext->newInstanceWithoutConstructor();
        $asyncAssertionContextConstructor = $reflectedAsyncAssertionContext->getConstructor();
        $asyncAssertionContextConstructor->invoke($asyncAssertionContext, $customAssertionContext);

        $reflectedExpectationContext = new \ReflectionClass(ExpectationContext::class);
        $fakeTestModel = new TestModel('SomeClass', 'someMethod');
        $expectationContext = $reflectedExpectationContext->newInstanceWithoutConstructor();
        $expectationContextConstructor = $reflectedExpectationContext->getConstructor();
        $expectationContextConstructor->invoke($expectationContext, $fakeTestModel, $assertionContext, $asyncAssertionContext, $mockBridge);

        $reflectedSubject = new \ReflectionClass($testCase);
        $constructor = $reflectedSubject->getConstructor();
        $subject = $reflectedSubject->newInstanceWithoutConstructor();
        $constructor->invoke(
            $subject,
            (new \ReflectionClass(ImplicitTestSuite::class))->newInstanceWithoutConstructor(),
            $assertionContext,
            $asyncAssertionContext,
            $expectationContext,
            $mockBridge
        );

        return [$subject, $assertionContext, $asyncAssertionContext, $customAssertionContext, $expectationContext];
    }
}