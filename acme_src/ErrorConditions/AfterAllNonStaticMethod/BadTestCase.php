<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\AfterAllNonStaticMethod;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class BadTestCase extends TestCase {

    #[AfterAll]
    public function badAfterAllMustBeStatic() {

    }

    #[Test]
    public function ensureSomething() {

    }

    public function getName() : string {
        return self::class;
    }
}