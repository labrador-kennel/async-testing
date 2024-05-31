<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\SingleMockTest;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Configuration\Configuration;
use Labrador\AsyncUnit\Framework\TestCase;

class MyTestCase extends TestCase {

    private ?object $createdMock = null;

    #[Test]
    public function checkCreatingMockObject() {
        $this->createdMock = $this->mocks()->createMock(Configuration::class);
        $this->assert->not()->isNull($this->createdMock);
    }

    public function getCreatedMock() : ?object {
        return $this->createdMock;
    }


}