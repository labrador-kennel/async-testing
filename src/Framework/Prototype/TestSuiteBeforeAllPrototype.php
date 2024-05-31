<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Prototype;


use Amp\Coroutine;
use Amp\Promise;
use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\Prototype;
use Labrador\AsyncUnit\Framework\Attribute\PrototypeRequiresAttribute;
use Labrador\AsyncUnit\Framework\TestSuite;
use Generator;

#[Prototype([TestSuite::class])]
#[PrototypeRequiresAttribute(BeforeAll::class)]
interface TestSuiteBeforeAllPrototype {

    public function beforeAll() : Promise|Generator|Coroutine|null;

}