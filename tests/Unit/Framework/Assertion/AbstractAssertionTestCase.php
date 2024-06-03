<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use PHPUnit\Framework\TestCase;

abstract class AbstractAssertionTestCase extends TestCase {

    abstract protected function getAssertion($expected, $actual) : Assertion;

    abstract protected function getExpected() : mixed;

    abstract public static function getGoodActual() : array;

    abstract public static function getBadActual() : array;

    abstract protected function getSummaryAssertionMessageClass() : string;

    abstract protected function getDetailsAssertionMessageClass() : string;

    /**
     * @dataProvider getGoodActual
     */
    public function testAssertGoodValueEqualsGoodValue(mixed $actual) : void {
        $subject = $this->getAssertion($this->getExpected(), $actual);
        $results = $subject->assert();

        $this->assertTrue($results->isSuccessful());
        $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
        $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
    }

    /**
     * @dataProvider getBadActual
     */
    public function testAssertGoodValueDoesNotEqualBadValueInformation(mixed $actual) : void {
        $subject = $this->getAssertion($this->getExpected(), $actual);
        $results = $subject->assert();

        $this->assertFalse($results->isSuccessful());
        $this->assertInstanceOf($this->getSummaryAssertionMessageClass(), $results->getSummary());
        $this->assertInstanceOf($this->getDetailsAssertionMessageClass(), $results->getDetails());
    }

}