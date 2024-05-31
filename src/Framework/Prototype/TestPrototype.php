<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Prototype;

use Amp\Coroutine;
use Amp\Promise;
use Labrador\AsyncUnit\Framework\Attribute\Prototype;
use Labrador\AsyncUnit\Framework\Attribute\PrototypeRequiresAttribute;
use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use Generator;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(Test::class)]
interface TestPrototype {

    public function test() : Promise|Generator|Coroutine|null;

}