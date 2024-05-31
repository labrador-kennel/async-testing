<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasAssertionPlugin;

use Countable;
use Labrador\AsyncUnit\Framework\Assertion\AssertIsTrue;
use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Framework\Plugin\CustomAssertionPlugin;
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