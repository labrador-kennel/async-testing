<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MultipleTest;

use Amp\Delayed;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use function Amp\async;
use function Amp\call;
use function Amp\delay;

class MyTestCase extends TestCase {

    private array $invoked = [];

    #[Test]
    public function ensureSomethingHappens() {
        delay(0.100);
        $this->invoked[] = __METHOD__;
        $this->assert->stringEquals('foo', 'foo');
    }

    #[Test]
    public function ensureSomethingHappensTwice() {
        $this->invoked[] = __METHOD__;
        $this->assert->not()->stringEquals('AsyncUnit', 'PHPUnit');
    }

    #[Test]
    public function ensureSomethingHappensThreeTimes() {
        return async(function() {
            $this->invoked[] = self::class . '::ensureSomethingHappensThreeTimes';
            $this->assert->intEquals(42, 42);
        });
    }

    public function getName() : string {
        return self::class;
    }

    public function getInvokedMethods() : array {
        return $this->invoked;
    }
}