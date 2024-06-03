<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\BeforeEachTestSuiteHook;

use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    private array $state = [];

    #[BeforeEach]
    public function addToState() : void {
        $this->state[] = 'AsyncUnit';
    }

    public function getState() : array {
        return $this->state;
    }

}