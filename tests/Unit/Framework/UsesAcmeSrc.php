<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

trait UsesAcmeSrc {

    private function path(string $path) : string {
        return dirname(__DIR__, 3) . '/acme_src/' . $path;
    }

    private function implicitDefaultTestSuitePath(string $path) : string {
        return $this->path('ImplicitDefaultTestSuite/' . $path);
    }

    private function explicitTestSuitePath(string $path) : string {
        return $this->path('ExplicitTestSuite/' . $path);
    }

}