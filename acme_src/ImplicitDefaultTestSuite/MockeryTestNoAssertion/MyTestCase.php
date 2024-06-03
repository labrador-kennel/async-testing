<?php declare(strict_types=1);

namespace Acme\DemoSuites\ImplicitDefaultTestSuite\MockeryTestNoAssertion;

use Labrador\AsyncUnit\Framework\Attribute\Test;
use Labrador\AsyncUnit\Framework\Configuration\Configuration;
use Labrador\AsyncUnit\Framework\TestCase;
use Mockery\MockInterface;

class MyTestCase extends TestCase {

    #[Test]
    public function checkMockExpectations() : void {
        /** @var MockInterface $mock */
        $mock = $this->mocks()->createMock(Configuration::class);

        $mock->expects()->getTestDirectories()->andReturn([]);

        $mock->getTestDirectories();
    }

}