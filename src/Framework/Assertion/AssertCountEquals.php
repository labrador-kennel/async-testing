<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Assertion;

use Countable;
use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\CountEqualsMessage;

final class AssertCountEquals implements Assertion {

    public function __construct(private int $expected, private array|Countable $actual) {}

    public function assert() : AssertionResult {
        $factoryMethod = count($this->actual) === $this->expected ? 'validAssertion' : 'invalidAssertion';
        return AssertionResultFactory::$factoryMethod(
            $this->getSummary(),
            $this->getDetails()
        );
    }

    private function getSummary() : AssertionMessage {
        return new CountEqualsMessage($this->expected, $this->actual);
    }

    private function getDetails() : AssertionMessage {
        return new CountEqualsMessage($this->expected, $this->actual);
    }
}