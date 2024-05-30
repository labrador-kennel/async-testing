<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Configuration;

interface ConfigurationFactory {

    public function make(string $path) : Configuration;

}
