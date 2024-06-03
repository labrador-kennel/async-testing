<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Randomizer;

interface Randomizer {

    /**
     * @template ItemType
     * @param list<ItemType> $items
     * @return list<ItemType>
     */
    public function randomize(array $items) : array;

}