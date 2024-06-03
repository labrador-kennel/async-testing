<?php

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\TestCaseHooksPriority;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    private static array $invokedAll = [];

    private array $invokedEach = [];

    #[BeforeAll(3)]
    public static function beforeAllThree() {
        self::$invokedAll[] = __FUNCTION__;
    }

    #[BeforeAll(1)]
    public static function beforeAllOne() {
        self::$invokedAll[] = __FUNCTION__;
    }

    #[BeforeAll(2)]
    public static function beforeAllTwo() {
        self::$invokedAll[] = __FUNCTION__;
    }

    #[BeforeEach(2)]
    public function beforeEachTwo() {
        $this->invokedEach[] = __FUNCTION__;
    }

    #[BeforeEach(1)]
    public function beforeEachOne() {
        $this->invokedEach[] = __FUNCTION__;
    }

    #[BeforeEach(3)]
    public function beforeEachThree() {
        $this->invokedEach[] = __FUNCTION__;
    }

    #[Test]
    public function testSomething() {
         $this->assert->stringEquals('AsyncUnit', 'AsyncUnit');
    }

    #[AfterEach(1)]
    public function afterEachOne() {
        $this->invokedEach[] = __FUNCTION__;
    }

    #[AfterEach(3)]
    public function afterEachThree() {
        $this->invokedEach[] = __FUNCTION__;
    }

    #[AfterEach(2)]
    public function afterEachTwo() {
        $this->invokedEach[] = __FUNCTION__;
    }

    #[AfterAll(3)]
    public static function afterAllThree() {
        self::$invokedAll[] = __FUNCTION__;
    }

    #[AfterAll(2)]
    public static function afterAllTwo() {
        self::$invokedAll[] = __FUNCTION__;
    }

    #[AfterAll(1)]
    public static function afterAllOne() {
        self::$invokedAll[] = __FUNCTION__;
    }

    public static function clearInvokedAll() {
        self::$invokedAll = [];
    }

    public function getInvokedAll() : array {
        return self::$invokedAll;
    }

    public function getInvokedEach() : array {
        return $this->invokedEach;
    }


}