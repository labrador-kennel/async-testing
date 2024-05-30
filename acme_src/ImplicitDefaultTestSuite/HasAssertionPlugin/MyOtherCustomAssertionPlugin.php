<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasAssertionPlugin;

use Countable;
use Cspray\Labrador\AsyncUnit\Assertion\AssertIsTrue;
use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\CustomAssertionPlugin;
use Stringable;

class MyOtherCustomAssertionPlugin implements Countable, Stringable, CustomAssertionPlugin {

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : void {
        $customAssertionContext->registerAssertion('myOtherCustomAssertion', function() {
            return new AssertIsTrue(true);
        });
    }

    public function __toString() {
        return '';
    }

    public function count() : int {
        return 0;
    }
}