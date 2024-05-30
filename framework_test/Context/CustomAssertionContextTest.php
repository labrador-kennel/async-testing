<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use PHPUnit\Framework\TestCase;

class CustomAssertionContextTest extends TestCase {

    private CustomAssertionContext $subject;

    public function setUp() : void {
        $reflectedClass = new \ReflectionClass(CustomAssertionContext::class);
        $this->subject = $reflectedClass->newInstanceWithoutConstructor();
    }

    public function testHasAssertionContextFalseIfEmpty() {
        $this->assertFalse($this->subject->hasRegisteredAssertion('someMethodName'));
    }

    public function testHasAsyncAssertionContextFalseIfEmpty() {
        $this->assertFalse($this->subject->hasRegisteredAsyncAssertion('someMethodName'));
    }

    public function testRegisterAssertionWithInvalidName() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A registered custom assertion must have a valid method name but "bad value with spaces" was provided');

        $this->subject->registerAssertion('bad value with spaces', function() {});
    }

    public function testRegisterAsyncAssertionWithInvalidName() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A registered custom async assertion must have a valid method name but "bad value with spaces" was provided');

        $this->subject->registerAsyncAssertion('bad value with spaces', function() {});
    }

    public function testRegisterAssertionHasAssertionReturnsTrue() {
        $this->subject->registerAssertion('ensureCustomThing', function() {});

        $this->assertTrue($this->subject->hasRegisteredAssertion('ensureCustomThing'));
    }

    public function testRegisterAsyncAssertionHasAssertionReturnsTrue() {
        $this->subject->registerAsyncAssertion('ensureSomeThing', function() {});

        $this->assertTrue($this->subject->hasRegisteredAsyncAssertion('ensureSomeThing'));
    }

    public function testCreateAssertionDoesNotExistThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no custom assertion registered for "customAssertionName".');

        $this->subject->createAssertion('customAssertionName');
    }

    public function testCreateAsyncAssertionDoesNotExistThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no custom async assertion registered for "customAssertionName".');

        $this->subject->createAsyncAssertion('customAssertionName');
    }

    public function testCreateRegisteredFactoryDoesNotReturnAssertionThrowsException() {
        $this->subject->registerAssertion('ensureSomething', fn() => 'not an assertion');

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('The factory for custom assertion "ensureSomething" must return an instance of ' . Assertion::class);

        $this->subject->createAssertion('ensureSomething');
    }

    public function testCreateRegisteredFactoryDoesNotReturnAsyncAssertionThrowsException() {
        $this->subject->registerAsyncAssertion('ensureSomething', fn() => 'not an assertion');

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('The factory for custom async assertion "ensureSomething" must return an instance of ' . AsyncAssertion::class);

        $this->subject->createAsyncAssertion('ensureSomething');
    }

    public function testCreateRegisteredFactoryIsAssertionReturnsObject() {
        $assertion = $this->getMockBuilder(Assertion::class)->getMock();
        $this->subject->registerAssertion('ensureSomething', fn() => $assertion);

        $actual = $this->subject->createAssertion('ensureSomething');

        $this->assertSame($assertion, $actual);
    }

    public function testCreateRegisteredFactoryIsAsyncAssertionReturnsObject() {
        $assertion = $this->getMockBuilder(AsyncAssertion::class)->getMock();
        $this->subject->registerAsyncAssertion('ensureSomething', fn() => $assertion);

        $actual = $this->subject->createAsyncAssertion('ensureSomething');

        $this->assertSame($assertion, $actual);
    }

    public function testRegisteredAssertionFactoryReceivesArgs() {
        $assertion = $this->getMockBuilder(Assertion::class)->getMock();
        $state = new \stdClass();
        $state->args = null;
        $this->subject->registerAssertion('ensureSomething', function(...$args) use($state, $assertion) {
            $state->args = $args;
            return $assertion;
        });

        $this->subject->createAssertion('ensureSomething', 1, 'a', 'b', ['1', '2', 3]);
        $this->assertNotNull($state->args);
        $this->assertSame([1, 'a', 'b', ['1', '2', 3]], $state->args);
    }

    public function testRegisteredAsyncAssertionFactoryReceivesArgs() {
        $assertion = $this->getMockBuilder(AsyncAssertion::class)->getMock();
        $state = new \stdClass();
        $state->args = null;
        $this->subject->registerAsyncAssertion('ensureSomething', function(...$args) use($state, $assertion) {
            $state->args = $args;
            return $assertion;
        });

        $this->subject->createAsyncAssertion('ensureSomething', 1, 'a', 'b', ['1', '2', 3]);
        $this->assertNotNull($state->args);
        $this->assertSame([1, 'a', 'b', ['1', '2', 3]], $state->args);
    }
}