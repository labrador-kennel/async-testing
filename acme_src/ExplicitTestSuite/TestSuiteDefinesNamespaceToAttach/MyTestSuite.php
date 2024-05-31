<?php

namespace Acme\DemoSuites\ExplicitTestSuite\TestSuiteDefinesNamespaceToAttach;

use Labrador\AsyncUnit\Framework\TestSuite;

class MyTestSuite extends TestSuite {

    public static function getNamespacesToAttach(): array {
        return [
            'Acme\\\\DemoSuites\\\\ExplicitTestSuite\\\\TestSuiteDefinesNamespaceToAttach\\\\HasExplicitTestSuite'
        ];
    }

}