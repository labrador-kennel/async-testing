<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Labrador\AsyncUnit\Framework\Randomizer\ShuffleRandomizer;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class ShuffleRandomizerTest extends PHPUnitTestCase {

    public function testShuffleRandomizerReturnsArrayWithSameElements() : void {
        $subject = new ShuffleRandomizer();

        $expected = ['a', 'b', 'c', 'd'];
        $actual = $subject->randomize(['d', 'b', 'c', 'a']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

}