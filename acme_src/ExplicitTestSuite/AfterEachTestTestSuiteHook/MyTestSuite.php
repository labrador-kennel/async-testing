<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AfterEachTestTestSuiteHook;

use Labrador\AsyncUnit\Framework\Attribute\AfterEachTest;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    private array $state = [];

    #[AfterEachTest]
    public function addToState() : void {
        $this->state[] = 'AsyncUnit';
    }

    public function getState() : array {
        return $this->state;
    }

}