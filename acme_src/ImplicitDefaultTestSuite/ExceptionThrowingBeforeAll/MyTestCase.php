<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingBeforeAll;


use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[BeforeAll]
    public static function beforeAll() {
        throw new \RuntimeException('Thrown in the class beforeAll');
    }

    #[Test]
    public static function ensureSomething() {

    }


}