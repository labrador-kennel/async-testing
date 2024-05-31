<?php declare(strict_types=1);


namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDisabledCustomMessage;


use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite;
use Labrador\AsyncUnit\Framework\TestCase;

#[AttachToTestSuite(MyTestSuite::class)]
class MyTestCase extends TestCase {

    #[Test]
    public function testOne() {

    }

}