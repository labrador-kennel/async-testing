<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast;

use Amp\Future;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\DataProvider;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class FoodAndBeverageTestCase extends TestCase {

    public function foodProvider() {
        return [
            ['Bacon', 'Bacon'],
            ['Eggs', 'Eggs'],
            ['Hash Browns', 'Hash Browns'],
            ['Grits', 'Grits']
        ];
    }

    #[Test]
    #[DataProvider('foodProvider')]
    public function checkFood(string $a, string $b) {
        $this->asyncAssert()->stringEquals($a, Future::complete($b));
    }

}