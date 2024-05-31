<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\WithAsyncUnitConfig\tests\Foo;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class BarTestCase extends TestCase {

    #[Test]
    public function ensureStringEquals() {
        $this->assert->not()->stringEquals('foo', 'bar');
    }

}