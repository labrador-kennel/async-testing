<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasSingleAfterAllHook;

use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    private static array $classData = [];
    private array $objectData = [];

    #[AfterAll]
    public static function afterAll() {
        self::$classData[] = 'afterAll';
    }

    #[Test]
    public function ensureSomething() {
        $this->objectData[] = 'ensureSomething';
    }

    #[Test]
    public function ensureSomethingTwice() {
        $this->objectData[] = 'ensureSomethingTwice';
    }

    public function getCombinedData() : array {
        return array_merge([], self::$classData, $this->objectData);
    }
}