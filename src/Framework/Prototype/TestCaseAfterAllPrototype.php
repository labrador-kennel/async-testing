<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Prototype;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\AfterAll;
use Labrador\AsyncUnit\Framework\Attribute\Prototype;
use Labrador\AsyncUnit\Framework\Attribute\PrototypeRequiresAttribute;
use Labrador\AsyncUnit\Framework\TestCase;
use Labrador\AsyncUnit\Framework\TestSuite;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(AfterAll::class)]
interface TestCaseAfterAllPrototype {

    public static function afterAll(TestSuite $testSuite) : Future|null;

}