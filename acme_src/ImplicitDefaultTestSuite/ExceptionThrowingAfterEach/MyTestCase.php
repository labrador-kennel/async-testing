<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingAfterEach;


use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    #[AfterEach]
    public function afterEach() {
        throw new \RuntimeException('Thrown in the object afterEach');
    }

    #[Test]
    public static function ensureSomething() {

    }


}