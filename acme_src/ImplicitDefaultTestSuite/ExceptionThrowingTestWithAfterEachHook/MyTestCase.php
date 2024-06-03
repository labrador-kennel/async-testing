<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\ExceptionThrowingTestWithAfterEachHook;

use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    private bool $afterHookCalled = false;

    #[Test]
    public function throwsException() {
        throw new \Exception('Test failure');
    }

    #[AfterEach]
    public function afterExceptionThrown() {
        $this->afterHookCalled = true;
    }

    public function getAfterHookCalled() {
        return $this->afterHookCalled;
    }

}