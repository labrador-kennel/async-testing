<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\HasAssertionPlugin;

use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;
use Cspray\Labrador\AsyncUnit\CustomAssertionPlugin;

class MyCustomAssertionPlugin implements CustomAssertionPlugin {

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : void {
    }

}