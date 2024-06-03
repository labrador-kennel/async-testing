<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Context;

use Throwable;

interface TestExpector {

    /**
     * @param class-string<Throwable> $type
     */
    public function exception(string $type) : void;

    public function exceptionMessage(string $message) : void;

    public function noAssertions() : void;

}