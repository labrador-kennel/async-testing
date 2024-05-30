<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestDisabledHookNotInvoked;

use Amp\Future;
use Amp\Success;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\BeforeEach;
use Cspray\Labrador\AsyncUnit\Attribute\Disabled;
use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    private array $state = [];

    #[BeforeEach]
    public function before() {
        $this->state[] = 'before';
    }

    #[Test]
    public function enabledTest() {
        $this->state[] = 'enabled';
        $this->asyncAssert()->arrayEquals(['before', 'enabled'], Future::complete($this->state));
    }

    #[Test]
    #[Disabled]
    public function disabledTest() {
        $this->state[] = 'disabled';
    }

    #[AfterEach]
    public function after() {
        $this->state[] = 'after';
    }

    public function getState() : array {
        return $this->state;
    }

}