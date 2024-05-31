<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestDisabledHookNotInvoked;

use Amp\Future;
use Amp\Success;
use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    private array $state = [];

    #[BeforeEach]
    public function before() {
        $this->state[] = 'before';
    }

    #[Test]
    public function enabledTest() {
        $this->state[] = 'enabled';
        $this->assert->arrayEquals(['before', 'enabled'], $this->state);
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