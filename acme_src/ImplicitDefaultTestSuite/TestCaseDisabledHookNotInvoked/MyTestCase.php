<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestCaseDisabledHookNotInvoked;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

#[Disabled]
class MyTestCase extends TestCase {

    private static array $state = [];

    #[BeforeAll]
    public static function beforeAll() {
        self::$state[] = 'beforeAll';
    }

    #[BeforeEach]
    public function before() {
        self::$state[] = 'before';
    }

    #[Test]
    public function testOne() {
        self::$state[] = 'testOne';
    }

    #[Test]
    public function testTwo() {
        self::$state[] = 'testTwo';
    }

    #[AfterEach]
    public function afterEach() {
        self::$state[] = 'afterEach';
    }

    #[AfterAll]
    public static function afterAll() {
        self::$state[] = 'afterAll';
    }

    public function getState() : array {
        return self::$state;
    }

}