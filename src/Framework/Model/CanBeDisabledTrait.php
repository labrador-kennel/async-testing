<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Model;


trait CanBeDisabledTrait {

    private bool $isDisabled = false;
    private ?string $disabledReason = null;

    public function isDisabled() : bool {
        return $this->isDisabled;
    }

    public function markDisabled(string $disabledReason = null) : void {
        $this->disabledReason = $disabledReason;
        $this->isDisabled = true;
    }

    public function getDisabledReason() : ?string {
        return $this->disabledReason;
    }

}