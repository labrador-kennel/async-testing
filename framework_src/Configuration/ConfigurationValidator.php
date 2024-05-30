<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Configuration;

interface ConfigurationValidator {

    public function validate(Configuration $configuration) : ConfigurationValidationResults;

}