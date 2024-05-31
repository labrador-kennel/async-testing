<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Model;


use Labrador\AsyncUnit\Framework\HookType;

trait HookAware {

    private array $hooks = [];

    /**
     * @param HookType $hookType
     * @return HookModel[]
     */
    public function getHooks(HookType $hookType) : array {
        return $this->hooks[$hookType->name] ?? [];
    }

    public function addHook(HookModel $hook) : void {
        if (!isset($this->hooks[$hook->getType()->name])) {
            $this->hooks[$hook->getType()->name] = [];
        }
        $this->hooks[$hook->getType()->name][] = $hook;
    }

}