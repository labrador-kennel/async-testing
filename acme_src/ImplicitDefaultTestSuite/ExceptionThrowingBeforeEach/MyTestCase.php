<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingBeforeEach;


use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[BeforeEach]
    public function beforeEach() {
        throw new \RuntimeException('Thrown in the object beforeEach');
    }

    #[Test]
    public static function ensureSomething() {

    }


}