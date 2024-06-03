<?php declare(strict_types=1);

namespace Acme\DemoSuites\ErrorConditions\NoTestsTestCase;

use Labrador\AsyncUnit\Framework\TestCase;

class BadTestCase extends TestCase {

    public function getName() {
        return self::class;
    }

    // We forgot to mark this as a Test
    public function ensureSomethingHappens() {

    }
}