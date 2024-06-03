<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\KitchenSink\WhatAbout;

use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\TestSuite;

class PotatoTestSuite extends TestSuite {

    #[BeforeAll]
    public function setBestHobbit() {
        $this->set('bestHobbit', 'Samwise');
    }


}