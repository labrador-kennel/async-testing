<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Stub;

class CountableStub implements \Countable {

    private int $count;

    public function __construct(int $count) {
        $this->count = $count;
    }

    public function count() : int {
        return $this->count;
    }
}