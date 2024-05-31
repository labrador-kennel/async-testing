<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\AssertInstanceOf;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\InstanceOfMessage;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\TrueUnaryOperandSummary;
use Labrador\AsyncUnit\Framework\Exception\InvalidArgumentException;
use Labrador\AsyncUnit\Framework\Exception\InvalidStateException;
use PHPUnit\Framework\TestCase;

class AssertInstanceOfTest extends TestCase {

    public function testPassExpectedStringNotClassThrowsException() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf(
            'The expected value must be a valid class but %s was given', var_export('not a class', true)
        ));
        new AssertInstanceOf('not a class', new \stdClass());
    }

    public function testInstanceOfInterfaceIsValid() {
        $subject = new AssertInstanceOf(AssertionMessage::class, new TrueUnaryOperandSummary('something'));
        $results = $subject->assert();

        $this->assertTrue($results->isSuccessful());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
    }

    public function testInstanceOfTypeIsNotInstance() {
        $subject = new AssertInstanceOf(TestCase::class, new TrueUnaryOperandSummary('foo'));
        $results = $subject->assert();

        $this->assertFalse($results->isSuccessful());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
    }

    public function testPassingObjectAsExpected() {
        $subject = new AssertInstanceOf(new InvalidStateException(), new InvalidArgumentException());
        $results = $subject->assert();

        $this->assertFalse($results->isSuccessful());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getSummary());
        $this->assertInstanceOf(InstanceOfMessage::class, $results->getDetails());
    }

}