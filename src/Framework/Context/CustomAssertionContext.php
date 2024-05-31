<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Context;

use Labrador\AsyncUnit\Framework\Assertion\Assertion;
use Labrador\AsyncUnit\Framework\Exception\InvalidArgumentException;
use Labrador\AsyncUnit\Framework\Exception\InvalidStateException;

final class CustomAssertionContext {

    private const VALID_METHOD_NAME_REGEX = '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/';

    private array $assertions = [];

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