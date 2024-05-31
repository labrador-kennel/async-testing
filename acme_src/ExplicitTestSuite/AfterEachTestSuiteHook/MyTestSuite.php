<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AfterEachTestSuiteHook;

use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    private array $state = [];

    #[AfterEach]
    public function addToState() : void {
        $this->state[] = 'AsyncUnit';
    }

    public function getState() : array {
        return $this->state;
    }

}