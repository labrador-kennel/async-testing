<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast;

use Amp\Future;
use Amp\Success;
use Labrador\AsyncUnit\Framework\Attribute\DataProvider;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

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
        $this->assert->stringEquals($a, $b);
    }

}