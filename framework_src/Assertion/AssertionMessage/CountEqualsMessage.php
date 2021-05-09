<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Assertion\AssertionMessage;

use Countable;
use Cspray\Labrador\AsyncUnit\AssertionMessage;

class CountEqualsMessage implements AssertionMessage {

    public function __construct(private int $expected, private array|Countable $actual) {}

    public function toString() : string {
        return sprintf(
            'asserting %s with count of %d equals expected count of %d',
            is_array($this->actual) ? 'array' : $this->actual::class,
            count($this->actual),
            $this->expected
        );
    }

    public function toNotString() : string {
        return sprintf(
            'asserting %s with count of %d does not equal expected count of %d',
            is_array($this->actual) ? 'array': $this->actual::class,
            count($this->actual),
            $this->expected
        );
    }
}