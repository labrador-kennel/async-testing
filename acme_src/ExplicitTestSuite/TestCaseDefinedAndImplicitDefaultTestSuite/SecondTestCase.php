<?php declare(strict_types=1);


namespace Acme\DemoSuites\ExplicitTestSuite\TestCaseDefinedAndImplicitDefaultTestSuite;


use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite;
use Labrador\AsyncUnit\Framework\TestCase;

#[AttachToTestSuite(MyTestSuite::class)]
class SecondTestCase extends TestCase {

    #[Test]
    public function ensureSomething() {
        $this->assert->isFalse(false);
    }

}