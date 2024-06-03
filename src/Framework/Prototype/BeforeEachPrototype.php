<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Prototype;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\BeforeEach;
use Labrador\AsyncUnit\Framework\Attribute\Prototype;
use Labrador\AsyncUnit\Framework\Attribute\PrototypeRequiresAttribute;
use Labrador\AsyncUnit\Framework\TestCase;
use Labrador\AsyncUnit\Framework\TestSuite;

#[Prototype([TestSuite::class, TestCase::class])]
#[PrototypeRequiresAttribute(BeforeEach::class)]
interface BeforeEachPrototype {

    public function beforeEach() : Future|null;

}