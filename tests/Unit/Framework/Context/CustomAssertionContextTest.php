<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Context;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Framework\Exception\InvalidArgumentException;
use Labrador\AsyncUnit\Framework\Exception\InvalidStateException;
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

    public function testRegisterAssertionWithInvalidName() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A registered custom assertion must have a valid method name but "bad value with spaces" was provided');

        $this->subject->registerAssertion('bad value with spaces', function() {});
    }

    public function testRegisterAssertionHasAssertionReturnsTrue() {
        $this->subject->registerAssertion('ensureCustomThing', function() {});

        $this->assertTrue($this->subject->hasRegisteredAssertion('ensureCustomThing'));
    }

    public function testCreateAssertionDoesNotExistThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('There is no custom assertion registered for "customAssertionName".');

        $this->subject->createAssertion('customAssertionName');
    }

    public function testCreateRegisteredFactoryDoesNotReturnAssertionThrowsException() {
        $this->subject->registerAssertion('ensureSomething', fn() => 'not an assertion');

        $this->expectException(InvalidStateException::class);
        $this->expectExceptionMessage('The factory for custom assertion "ensureSomething" must return an instance of ' . Assertion::class);

        $this->subject->createAssertion('ensureSomething');
    }

    public function testCreateRegisteredFactoryIsAssertionReturnsObject() {
        $assertion = $this->getMockBuilder(Assertion::class)->getMock();
        $this->subject->registerAssertion('ensureSomething', fn() => $assertion);

        $actual = $this->subject->createAssertion('ensureSomething');

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
}