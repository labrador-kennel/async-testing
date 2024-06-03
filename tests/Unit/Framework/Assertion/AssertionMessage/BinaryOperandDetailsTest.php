<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Assertion\AssertionMessage;

use Labrador\AsyncUnit\Framework\Assertion\AssertionMessage\BinaryOperandDetails;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class BinaryOperandDetailsTest extends TestCase {

    public static function dataProvider() : array {
        return [
            ['string', 'bar'],
            ['integer', 1],
            ['float', 3.14],
            ['array', [1,2,3]],
            ['object', new \stdClass()],
            ['bool', true],
            ['null', null],
            ['resource', STDOUT]
        ];
    }

    #[DataProvider('dataProvider')]
    public function testToString(string $a, mixed $b) : void {
        $message = new BinaryOperandDetails($a, $b);
        $expected = sprintf(
            'comparing actual value %s equals %s',
            var_export($b, true),
            var_export($a, true)
        );
        $this->assertSame($expected, $message->toString());
    }

    #[DataProvider('dataProvider')]
    public function testToNotString(string $a, mixed $b) : void {
        $message = new BinaryOperandDetails($a, $b);
        $expected = sprintf(
            'comparing actual value %s does not equal %s',
            var_export($b, true),
            var_export($a, true)
        );
        $this->assertSame($expected, $message->toNotString());
    }
}