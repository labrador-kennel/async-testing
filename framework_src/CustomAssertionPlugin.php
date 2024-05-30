<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Cspray\Labrador\AsyncUnit\Context\CustomAssertionContext;

/**
 * Interface CustomAssertionPlugin
 * @package Cspray\Labrador\AsyncUnit
 */
interface CustomAssertionPlugin {

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : void;

}
