<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Configuration;

final class ConfigurationValidationResults {

    public function __construct(
        private readonly array $validationErrors
    ) {}

    public function isValid() : bool {
        return empty($this->validationErrors);
    }

    public function getValidationErrors() : array {
        return $this->validationErrors;
    }


}