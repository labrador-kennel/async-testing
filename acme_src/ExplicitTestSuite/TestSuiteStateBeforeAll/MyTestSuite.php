<?php declare(strict_types=1);

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteStateBeforeAll;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\DefaultTestSuite;
use Labrador\AsyncUnit\Framework\TestSuite;

#[DefaultTestSuite]
class MyTestSuite extends TestSuite {

    #[BeforeAll]
    public function setInitialState() {
        $this->set('foo', 'bar');
    }

}