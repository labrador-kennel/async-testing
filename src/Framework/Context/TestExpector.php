<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Context;

interface TestExpector {

    public function exception(string $type) : void;

    public function exceptionMessage(string $message) : void;

    public function noAssertions() : void;

}