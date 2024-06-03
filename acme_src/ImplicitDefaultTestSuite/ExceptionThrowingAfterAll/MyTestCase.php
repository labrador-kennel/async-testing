<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingAfterAll;


use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[AfterAll]
    public static function afterAll() {
        throw new \RuntimeException('Thrown in the class afterAll');
    }

    #[Test]
    public static function ensureSomething() {

    }

}