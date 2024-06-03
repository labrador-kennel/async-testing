<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Prototype;

use Amp\Future;
use Labrador\AsyncUnit\Framework\Attribute\BeforeAll;
use Labrador\AsyncUnit\Framework\Attribute\Prototype;
use Labrador\AsyncUnit\Framework\Attribute\PrototypeRequiresAttribute;
use Labrador\AsyncUnit\Framework\TestCase;
use Labrador\AsyncUnit\Framework\TestSuite;

#[Prototype([TestCase::class])]
#[PrototypeRequiresAttribute(BeforeAll::class)]
interface TestCaseBeforeAllPrototype {

    public static function beforeAll(TestSuite $testSuite) : Future|null;

}