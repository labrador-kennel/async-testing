<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Randomizer;

final class NullRandomizer implements Randomizer {

    public function randomize(array $items) : array {
        return $items;
    }

}