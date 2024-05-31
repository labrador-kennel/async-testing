<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasNotTestCaseObject;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    public function getName() {
        return self::class;
    }

    #[Test]
    public function ensureSomethingHappens() {

    }
}