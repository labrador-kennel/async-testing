<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Exception;

final class TestDisabledException extends Exception {

    public function __construct($message) {
        parent::__construct($message);
    }

}