<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Model;

trait MethodModelTrait {

    public function __construct(
        private string $class,
        private string $method
    ) {}

    public function getClass() : string {
        return $this->class;
    }

    public function getMethod() : string {
        return $this->method;
    }

}