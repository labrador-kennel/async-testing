<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Prototype;

use Amp\Coroutine;
use Amp\Promise;
use Labrador\AsyncUnit\Framework\Attribute\DataProvider;
use Labrador\AsyncUnit\Framework\Attribute\Prototype;
use Labrador\AsyncUnit\Framework\Attribute\PrototypeRequiresAttribute;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use Generator;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(DataProvider::class)]
#[PrototypeRequiresAttribute(Test::class)]
interface DataProviderTestPrototype {

    public function test(mixed ...$args) : Promise|Generator|Coroutine|null;

}