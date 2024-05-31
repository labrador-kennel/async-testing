<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabledHookNotInvoked;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\AfterEachTest;
use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEachTest;
use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\TestSuite;

#[Disabled]
class MyTestSuite extends TestSuite {

    private array $state = [];

    #[BeforeAll]
    public function beforeAll() {
        $this->state[] = 'beforeAll';
    }

    #[BeforeEach]
    public function beforeEachTestCase() {
        $this->state[] = 'beforeEach';
    }

    #[BeforeEachTest]
    public function beforeEachTest() {
        $this->state[] = 'beforeEachTest';
    }

    #[AfterEachTest]
    public function afterEachTest() {
        $this->state[] = 'afterEachTest';
    }

    #[AfterEach]
    public function afterEachTestCase() {
        $this->state[] = 'afterEach';
    }

    #[AfterAll]
    public function afterAll() {
        $this->state[] = 'afterAll';
    }

    public function getState() : array {
        return $this->state;
    }

}