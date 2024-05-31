<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasAssertionPlugin;

use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Framework\Plugin\CustomAssertionPlugin;

class MyCustomAssertionPlugin implements CustomAssertionPlugin {

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : void {
    }

}