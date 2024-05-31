<?php declare(strict_types=1);


namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinedAndImplicitDefaultTestSuite;


use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class FirstTestCase extends TestCase {

    #[Test]
    public function ensureSomething() {
        $this->assert->stringEquals('AsyncUnit', 'AsyncUnit');
    }

}