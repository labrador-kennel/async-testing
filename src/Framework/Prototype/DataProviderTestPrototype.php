<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Prototype;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\DataProvider;
use Labrador\AsyncUnit\Framework\Attribute\Prototype;
use Labrador\AsyncUnit\Framework\Attribute\PrototypeRequiresAttribute;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(DataProvider::class)]
#[PrototypeRequiresAttribute(Test::class)]
interface DataProviderTestPrototype {

    public function test(mixed ...$args) : Future|null;

}