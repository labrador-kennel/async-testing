<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\BeforeAllTestSuiteHook;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    private int $counter = 0;

    #[BeforeAll]
    public function incrementCounter() : void {
        $this->counter++;
    }

    public function getCounter() : int {
        return $this->counter;
    }

}