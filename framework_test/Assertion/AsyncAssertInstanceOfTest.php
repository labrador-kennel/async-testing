<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\InstanceOfMessage;
use Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage\TrueUnaryOperandSummary;
use Cspray\Labrador\AsyncUnit\AssertionMessage;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;
use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;

class AsyncAssertInstanceOfTest extends TestCase {

    public function testPassExpectedStringNotClassThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The expected value must be a valid class but %s was given', var_export('not a class', true)
        ));
        new AsyncAssertInstanceOf('not a class', Future::complete(new \stdClass()));
    }

    public function testInstanceOfInterfaceIsValid() {
            $subject = new AsyncAssertInstanceOf(AssertionMessage::class, Future::complete(new TrueUnaryOperandSummary('something')));
            $results = $subject->assert()->await();

            $this->assertTrue($results->isSuccessful());
            $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
            $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
    }

    public function testInstanceOfTypeIsNotInstance() {
        $subject = new AsyncAssertInstanceOf(TestCase::class, Future::complete(new TrueUnaryOperandSummary('foo')));
        $results = $subject->assert()->await();

        $this->assertFalse($results->isSuccessful());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
    }

    public function testPassingObjectAsExpected() {
        $subject = new AsyncAssertInstanceOf(new InvalidStateException(), Future::complete(new InvalidArgumentException()));
        $results = $subject->assert()->await();

        $this->assertFalse($results->isSuccessful());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
    }

}