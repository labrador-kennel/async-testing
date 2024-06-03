<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasSingleBeforeEachHook;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    private array $data = [];

    #[BeforeEach]
    public function beforeEach() {
        $this->data[]  = 'beforeEach';
    }

    #[Test]
    public function ensureSomething() {
        $this->data[] = 'ensureSomething';
    }

    #[Test]
    public function ensureSomethingTwice() {
        $this->data[] = 'ensureSomethingTwice';
    }

    public function getData() : array {
        return $this->data;
    }
}