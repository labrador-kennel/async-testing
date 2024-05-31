<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestCaseDisabled;

use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

#[Disabled]
class MyTestCase extends TestCase {

    private array $data = [];

    #[Test]
    public function skippedOne() {
        $this->data[] = '1';
    }

    #[Test]
    public function skippedTwo() {
        $this->data[] = '2';
    }

    #[Test]
    public function skippedThree() {
        $this->data[] = '3';
    }

    public function getData() : array {
        return $this->data;
    }

}