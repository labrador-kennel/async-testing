<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Amp\Future;
use Labrador\AsyncUnit\Test\Unit\Framework\AsyncAssertion;
use Generator;
use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;
use function Amp\async;

abstract class AbstractAsyncAssertionTestCase extends TestCase {

    abstract protected function getAssertion(mixed $expected, Future|Generator $actual) : AsyncAssertion;

    abstract protected function getExpected() : mixed;

    abstract public function getGoodActual() : array;

    abstract public function getBadActual() : array;

    abstract protected function getSummaryAssertionMessageClass() : string;

    abstract protected function getDetailsAssertionMessageClass() : string;

    /**
     * @dataProvider getGoodActual
     */
    public function testAssertGoodValueEqualsGoodValue(mixed $actual) {
        $suspension = EventLoop::getSuspension();
        EventLoop::defer(function() use($actual, $suspension) {
            try {
                $subject = $this->getAssertion($this->getExpected(), async(fn() => $actual));
                $results = $subject->assert()->await();

                $this->assertTrue($results->isSuccessful());
                $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
                $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
            } finally {
                $suspension->resume();
            }
        });
        $suspension->suspend();
    }

    /**
     * @dataProvider getBadActual
     */
    public function testAssertGoodValueDoesNotEqualBadValueInformation(mixed $actual) {
        $suspension = EventLoop::getSuspension();
        EventLoop::defer(function() use($actual, $suspension) {
            try {
                $subject = $this->getAssertion($this->getExpected(), async(fn() => $actual));
                $results = $subject->assert()->await();

                $this->assertFalse($results->isSuccessful());
                $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
                $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
            } finally {
                $suspension->resume();
            }
        });
        $suspension->suspend();
    }

}