<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasSingleAfterEachHook;

use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    private array $data = [];

    #[AfterEach]
    public function afterEach() {
        $this->data[] = 'afterEach';
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