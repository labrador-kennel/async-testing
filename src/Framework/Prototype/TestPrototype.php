<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Prototype;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\Prototype;
use Labrador\AsyncUnit\Framework\Attribute\PrototypeRequiresAttribute;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(Test::class)]
interface TestPrototype {

    public function test() : Future|null;

}