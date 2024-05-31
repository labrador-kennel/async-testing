<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Plugin;

use Labrador\AsyncUnit\Framework\Context\CustomAssertionContext;

/**
 * Interface CustomAssertionPlugin
 * @package Labrador\AsyncUnit\Framework
 */
interface CustomAssertionPlugin {

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : void;

}
