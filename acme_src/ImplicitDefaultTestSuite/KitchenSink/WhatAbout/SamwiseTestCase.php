<?php declare(strict_types=1);


namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\WhatAbout;

use Labrador\AsyncUnit\Framework\Attribute\AttachToTestSuite;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

#[AttachToTestSuite(PotatoTestSuite::class)]
class SamwiseTestCase extends TestCase {

    #[Test]
    public function isBestHobbit() {
        $this->assert->stringEquals('Samwise', $this->testSuite->get('bestHobbit'));
    }

}