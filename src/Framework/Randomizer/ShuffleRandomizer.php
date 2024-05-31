<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Randomizer;

final class ShuffleRandomizer implements Randomizer {

    public function randomize(array $items) : array {
        shuffle($items);
        return $items;
    }
}