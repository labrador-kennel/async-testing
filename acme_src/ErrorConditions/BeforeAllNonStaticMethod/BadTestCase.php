<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\BeforeAllNonStaticMethod;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class BadTestCase extends TestCase {

    #[BeforeAll]
    public function badBeforeAllMustBeStatic() {

    }

    #[Test]
    public function ensureSomething() {

    }

    public function getName() {
        return self::class;
    }
}