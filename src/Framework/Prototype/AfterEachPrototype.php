<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Prototype;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\AfterEach;
use Labrador\AsyncUnit\Framework\Attribute\Prototype;
use Labrador\AsyncUnit\Framework\Attribute\PrototypeRequiresAttribute;
use Labrador\AsyncUnit\Framework\TestCase;
use Labrador\AsyncUnit\Framework\TestSuite;

#[Prototype([TestSuite::class, TestCase::class])]
#[PrototypeRequiresAttribute(AfterEach::class)]
interface AfterEachPrototype {

    public function afterEach() : Future|null;

}