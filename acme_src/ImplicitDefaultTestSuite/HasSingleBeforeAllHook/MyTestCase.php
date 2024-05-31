<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasSingleBeforeAllHook;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use Generator;

class MyTestCase extends TestCase {

    private static array $staticData = [];
    private array $objectData = [];

    public function getName() : string {
        return self::class;
    }

    #[BeforeAll]
    public static function beforeAll() : void {
        self::$staticData[] = 'beforeAll';
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
        return array_merge([], self::$staticData, $this->objectData);
    }
}