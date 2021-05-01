<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\AnnotatedDefaultTestSuite;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    private ?string $testSuiteName = null;

    public function getTestSuiteName() : ?string {
        return $this->testSuiteName;
    }

    #[Test]
    public function ensureSomething() {
        $this->testSuiteName = $this->testSuite()->getName();
        $this->assert()->stringEquals(MyTestSuite::class, $this->testSuiteName);
    }


}