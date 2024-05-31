<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Randomizer;

interface Randomizer {

    public function randomize(array $items) : array;

}