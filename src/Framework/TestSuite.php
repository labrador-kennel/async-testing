<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

abstract class TestSuite {

    private array $data = [];

    private function __construct() {}

    public static function getNamespacesToAttach() : array {
        return [];
    }

    final public function getName() : string {
        return static::class;
    }

    final public function set(string $key, mixed $value) : void {
        $this->data[$key] = $value;
    }

    final public function get(string $key) : mixed {
        return $this->data[$key] ?? null;
    }

}