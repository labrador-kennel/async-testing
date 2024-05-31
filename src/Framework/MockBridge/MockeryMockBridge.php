<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\MockBridge;

use Labrador\AsyncUnit\Framework\Exception\MockFailureException;
use Mockery;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;
use Throwable;

final class MockeryMockBridge implements MockBridge {

    private array $createdMocks = [];

    public function initialize() : void {
        // Mockery requires no initialization
    }

    public function finalize() : void {
        try {
            Mockery::close();
        } catch (Throwable $exception) {
            throw new MockFailureException($exception->getMessage(), previous: $exception);
        }
    }

    public function createMock(string $class) : MockInterface|LegacyMockInterface {
        $mock = Mockery::mock($class);
        $this->createdMocks[] = $mock;
        return $mock;
    }

    public function getAssertionCount(): int {
        $count = 0;
        /** @var MockInterface $createdMock */
        foreach ($this->createdMocks as $createdMock) {
            $count += $createdMock->mockery_getExpectationCount();
        }
        return $count;
    }
}