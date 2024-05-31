<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasDataProvider;

use Labrador\AsyncUnit\Framework\Attribute\DataProvider;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    private int $counter = 0;

    public function myDataProvider() : array {
        return [
            ['foo', 'foo'],
            ['bar', 'bar'],
            ['baz', 'baz']
        ];
    }

    #[Test]
    #[DataProvider('myDataProvider')]
    public function ensureStringsEqual(string $expected, string $actual) : void {
        $this->counter++;
        $this->assert->stringEquals($expected, $actual);
    }

    public function getCounter() : int {
        return $this->counter;
    }
}