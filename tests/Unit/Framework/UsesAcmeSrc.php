<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

trait UsesAcmeSrc {

    private static function path(string $path) : string {
        return dirname(__DIR__, 3) . '/acme_src/' . $path;
    }

    private static function implicitDefaultTestSuitePath(string $path) : string {
        return self::path('ImplicitDefaultTestSuite/' . $path);
    }

    private static function explicitTestSuitePath(string $path) : string {
        return self::path('ExplicitTestSuite/' . $path);
    }

    private static function errorConditionsPath(string $path) : string {
        return self::path('ErrorConditions/' . $path);
    }

}