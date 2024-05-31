<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Model;

trait CanHaveTimeoutTrait {

    private ?int $timeout = null;

    public function setTimeout(int $timeout) : void {
        $this->timeout = $timeout;
    }

    public function getTimeout() : ?int {
        return $this->timeout;
    }

}