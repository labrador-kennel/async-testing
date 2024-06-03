<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Stub;

use Amp\Promise;
use Amp\Success;
use Labrador\AsyncUnit\Test\Unit\Framework\Context\CustomAssertionContext;
use Labrador\AsyncUnit\Test\Unit\Framework\CustomAssertionPlugin;

class FooAssertionPlugin implements CustomAssertionPlugin {

    private ?CustomAssertionContext $context = null;

    public function registerCustomAssertions(CustomAssertionContext $customAssertionContext) : Promise {
        $this->context = $customAssertionContext;
        return new Success();
    }

    public function getCustomAssertionContext() : ?CustomAssertionContext {
        return $this->context;
    }
}