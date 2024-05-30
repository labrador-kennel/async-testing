<?php declare(strict_types=1);


namespace Cspray\Labrador\AsyncUnit\Prototype;

use Amp\Future;
use Cspray\Labrador\AsyncUnit\Attribute\AfterEach;
use Cspray\Labrador\AsyncUnit\Attribute\Prototype;
use Cspray\Labrador\AsyncUnit\Attribute\PrototypeRequiresAttribute;
use Cspray\Labrador\AsyncUnit\TestCase;
use Cspray\Labrador\AsyncUnit\TestSuite;

#[Prototype([TestSuite::class, TestCase::class])]
#[PrototypeRequiresAttribute(AfterEach::class)]
interface AfterEachPrototype {

    public function afterEach() : Future|null;

}