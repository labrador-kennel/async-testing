<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion\AssertionMessage;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\EmptyUnaryOperandDetails;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EmptyUnaryOperandDetailsTest extends TestCase {

    public static function dataProvider() : array {
        return [
            ['foo'],
            [1],
            [3.14],
            [true],
            [new \stdClass()],
            [STDOUT],
            [[1,2,3]],
            [null]
        ];
    }

    #[DataProvider('dataProvider')]
    public function testToString(mixed $actual) {
        $message = new EmptyUnaryOperandDetails($actual);
        $expectedDetails = var_export($actual, true);
        if (is_null($actual)) {
            $expectedDetails = strtolower($expectedDetails);
        }
        $expected = sprintf('comparing %s is empty', $expectedDetails);
        $this->assertSame($expected, $message->toString());
    }

    /**
     * @dataProvider dataProvider
     */
    public function testToNotString(mixed $actual) {
        $message = new EmptyUnaryOperandDetails($actual);
        $expectedDetails = var_export($actual, true);
        if (is_null($actual)) {
            $expectedDetails = strtolower($expectedDetails);
        }
        $expected = sprintf('comparing %s is not empty', $expectedDetails);
        $this->assertSame($expected, $message->toNotString());
    }

}