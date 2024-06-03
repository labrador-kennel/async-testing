<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Configuration;

interface ConfigurationFactory {

    public function make(string $path) : Configuration;

}
