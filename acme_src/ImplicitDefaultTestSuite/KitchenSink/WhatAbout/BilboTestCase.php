<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\WhatAbout;

use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite;
use Labrador\AsyncUnit\Framework\Attribute\Disabled;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

#[AttachToTestSuite(PotatoTestSuite::class)]
class BilboTestCase extends TestCase {

    #[Test]
    #[Disabled]
    public function isBestHobbit() {
        throw new \RuntimeException('Bilbo doesn\'t come on this adventure.');
    }

}