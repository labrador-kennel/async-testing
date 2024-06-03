<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Configuration;

interface ConfigurationValidator {

    public function validate(Configuration $configuration) : ConfigurationValidationResults;

}