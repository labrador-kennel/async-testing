<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion\AssertionMessage;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\InstanceOfMessage;
use PHPUnit\Framework\TestCase;

class InstanceOfSummaryTest extends TestCase {

    public function testToStringExpectedIsString() {
        $instanceOfSummaryMessage = new InstanceOfMessage(AssertionMessage::class, new \stdClass());
        $expected = sprintf(
            'asserting object with type "stdClass" is an instanceof %s',
            AssertionMessage::class
        );
        $this->assertSame($expected, $instanceOfSummaryMessage->toString());
    }

    public function testToStringExpectedIsObject() {
        $instanceOfSummaryMessage = new InstanceOfMessage($this, new \stdClass());
        $expected = sprintf(
            'asserting object with type "stdClass" is an instanceof %s',
            $this::class
        );
        $this->assertSame($expected, $instanceOfSummaryMessage->toString());
    }

    public function testToNotStringExpectedIsString() {
        $instanceOfSummaryMessage = new InstanceOfMessage(AssertionMessage::class, new \stdClass());
        $expected = sprintf(
            'asserting object with type "stdClass" is not an instanceof %s',
            AssertionMessage::class
        );
        $this->assertSame($expected, $instanceOfSummaryMessage->toNotString());
    }

    public function testToNotStringExpectedIsObject() {
        $instanceOfSummaryMessage = new InstanceOfMessage($this, new \stdClass());
        $expected = sprintf(
            'asserting object with type "stdClass" is not an instanceof %s',
            $this::class
        );
        $this->assertSame($expected, $instanceOfSummaryMessage->toNotString());
    }

}