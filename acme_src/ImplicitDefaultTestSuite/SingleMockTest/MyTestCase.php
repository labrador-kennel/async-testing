<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\SingleMockTest;

use Cspray\Labrador\AsyncUnit\Attribute\Test;
use Cspray\Labrador\AsyncUnit\Configuration\Configuration;
use Cspray\Labrador\AsyncUnit\TestCase;

class MyTestCase extends TestCase {

    private ?object $createdMock = null;

    #[Test]
    public function checkCreatingMockObject() {
        $this->createdMock = $this->mocks()->createMock(Configuration::class);
        $this->assert()->not()->isNull($this->createdMock);
    }

    public function getCreatedMock() : ?object {
        return $this->createdMock;
    }


}