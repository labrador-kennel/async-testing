<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Stub;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use stdClass;

class MockAwareTestCase extends TestCase {

    private ?object $createdMock = null;

    #[Test]
    public function checkCreatingMock() {
        $this->createdMock = $this->mocks()->createMock(LoggerInterface::class);
        $this->assert->not()->isNull($this->createdMock);
    }

    public function getCreatedMock() : ?object {
        return $this->createdMock;
    }

}