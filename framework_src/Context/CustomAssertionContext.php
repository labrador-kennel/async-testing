<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Context;

use Cspray\Labrador\AsyncUnit\Assertion;
use Cspray\Labrador\AsyncUnit\AssertionResult;
use Cspray\Labrador\AsyncUnit\AsyncAssertion;
use Cspray\Labrador\AsyncUnit\Exception\InvalidArgumentException;
use Cspray\Labrador\AsyncUnit\Exception\InvalidStateException;

final class CustomAssertionContext {

    private const VALID_METHOD_NAME_REGEX = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';

    private array $assertions = [];
    private array $asyncAssertions = [];

    public function __construct() {}

    public function registerAssertion(string $methodName, callable $assertionFactory) : void {
        $this->ensureValidMethodName($methodName, 'assertion');
        $this->assertions[$methodName] = $assertionFactory;
    }

    public function hasRegisteredAssertion(string $methodName) : bool {
        return array_key_exists($methodName, $this->assertions);
    }

    public function createAssertion(string $methodName, mixed ...$args) : Assertion {
        if (!$this->hasRegisteredAssertion($methodName)) {
            throw new InvalidArgumentException(sprintf(
                'There is no custom assertion registered for "%s".',
                $methodName
            ));
        }
        $assertion = $this->assertions[$methodName](...$args);
        if (!$assertion instanceof Assertion) {
            $msg = sprintf(
                'The factory for custom assertion "%s" must return an instance of %s',
                $methodName,
                Assertion::class
            );
            throw new InvalidStateException($msg);
        }
        return $assertion;
    }

    public function registerAsyncAssertion(string $methodName, callable $asyncAssertionFactory) : void {
        $this->ensureValidMethodName($methodName, 'async assertion');
        $this->asyncAssertions[$methodName] = $asyncAssertionFactory;
    }

    public function hasRegisteredAsyncAssertion(string $methodName) : bool {
        return array_key_exists($methodName, $this->asyncAssertions);
    }

    public function createAsyncAssertion(string $methodName, mixed ...$args) : AsyncAssertion {
        if (!$this->hasRegisteredAsyncAssertion($methodName)) {
            throw new InvalidArgumentException(sprintf(
                'There is no custom async assertion registered for "%s".',
                $methodName
            ));
        }
        $assertion = $this->asyncAssertions[$methodName](...$args);
        if (!$assertion instanceof AsyncAssertion) {
            $msg = sprintf(
                'The factory for custom async assertion "%s" must return an instance of %s',
                $methodName,
                AsyncAssertion::class
            );
            throw new InvalidStateException($msg);
        }
        return $assertion;
    }

    private function ensureValidMethodName(string $methodName, string $assertionType) : void {
        if (!preg_match(self::VALID_METHOD_NAME_REGEX, $methodName)) {
            $msg = sprintf(
                'A registered custom %s must have a valid method name but "%s" was provided',
                $assertionType,
                $methodName
            );
            throw new InvalidArgumentException($msg);
        }
    }


}