<?php declare(strict_types=1);


namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabled;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite;
use Labrador\AsyncUnit\Framework\TestCase;

#[AttachToTestSuite(MyTestSuite::class)]
class FirstTestCase extends TestCase {

    #[Test]
    public function testOne() {
        throw new \RuntimeException();
    }

    #[Test]
    public function testTwo() {
        throw new \RuntimeException();
    }



}