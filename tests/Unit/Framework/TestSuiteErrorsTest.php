<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Labrador\AsyncUnit\Framework\Exception\TestCaseSetUpException;
use Labrador\AsyncUnit\Framework\Exception\TestCaseTearDownException;
use Labrador\AsyncUnit\Framework\Exception\TestSetupException;
use Labrador\AsyncUnit\Framework\Exception\TestSuiteSetUpException;
use Labrador\AsyncUnit\Framework\Exception\TestSuiteTearDownException;
use Labrador\AsyncUnit\Framework\Exception\TestTearDownException;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class TestSuiteErrorsTest extends PHPUnitTestCase {

    use UsesAcmeSrc;
    use TestSuiteRunnerScaffolding;

    public function setUp(): void {
        $this->buildTestSuiteRunner();
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingBeforeAllHaltsTestProcessing() {
        $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingBeforeAll');
        $results = $this->parser->parse($dir);

        $this->expectException(TestCaseSetUpException::class);
        $class = ImplicitDefaultTestSuite\ExceptionThrowingBeforeAll\MyTestCase::class;
        $this->expectExceptionMessage('Failed setting up "' . $class . '::beforeAll" #[BeforeAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the class beforeAll".');

        $this->testSuiteRunner->runTestSuites($results);
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingAfterAllHaltsTestProcessing() {
        $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingAfterAll');
        $results = $this->parser->parse($dir);

        $this->expectException(TestCaseTearDownException::class);
        $class = ImplicitDefaultTestSuite\ExceptionThrowingAfterAll\MyTestCase::class;
        $this->expectExceptionMessage('Failed tearing down "' . $class . '::afterAll" #[AfterAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the class afterAll".');

        $this->testSuiteRunner->runTestSuites($results);
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingBeforeEachHaltsTestProcessing() {
        $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingBeforeEach');
        $results = $this->parser->parse($dir);

        $this->expectException(TestSetUpException::class);
        $class = ImplicitDefaultTestSuite\ExceptionThrowingBeforeEach\MyTestCase::class;
        $this->expectExceptionMessage('Failed setting up "' . $class . '::beforeEach" #[BeforeEach] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the object beforeEach".');

        $this->testSuiteRunner->runTestSuites($results);
    }

    public function testImplicitDefaultTestSuiteExceptionThrowingAfterEachHaltsTestProcessing() {
        $dir = $this->implicitDefaultTestSuitePath('ExceptionThrowingAfterEach');
        $results = $this->parser->parse($dir);

        $this->expectException(TestTearDownException::class);
        $class = ImplicitDefaultTestSuite\ExceptionThrowingAfterEach\MyTestCase::class;
        $this->expectExceptionMessage('Failed tearing down "' . $class . '::afterEach" #[AfterEach] hook with exception of type "RuntimeException" with code 0 and message "Thrown in the object afterEach".');

        $this->testSuiteRunner->runTestSuites($results);
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteBeforeAllHaltsTestProcessing() {
        $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteBeforeAll');
        $results = $this->parser->parse($dir);

        $this->expectException(TestSuiteSetUpException::class);
        $class = ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeAll\MyTestSuite::class;
        $this->expectExceptionMessage('Failed setting up "' . $class . '::throwException" #[BeforeAll] hook with exception of type "RuntimeException" with code 0 and message "Thrown in AttachToTestSuite".');

        $this->testSuiteRunner->runTestSuites($results);
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteBeforeEachHaltsTestProcessing() {
        $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteBeforeEach');
        $results = $this->parser->parse($dir);

        $this->expectException(TestSuiteSetUpException::class);
        $class = ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeEach\MyTestSuite::class;
        $this->expectExceptionMessage('Failed setting up "' . $class . '::throwEachException" #[BeforeEach] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite BeforeEach".');

        $this->testSuiteRunner->runTestSuites($results);
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteAfterEachHaltsTestProcessing() {
        $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteAfterEach');
        $results = $this->parser->parse($dir);

        $this->expectException(TestSuiteTearDownException::class);
        $class = ExplicitTestSuite\ExceptionThrowingTestSuiteAfterEach\MyTestSuite::class;
        $this->expectExceptionMessage('Failed tearing down "' . $class . '::throwEachException" #[AfterEach] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite AfterEach".');

        $this->testSuiteRunner->runTestSuites($results);
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteAfterEachTestHaltsTestProcessing() {
        $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteAfterEachTest');
        $results = $this->parser->parse($dir);

        $this->expectException(TestTearDownException::class);
        $class = ExplicitTestSuite\ExceptionThrowingTestSuiteAfterEachTest\MyTestSuite::class;
        $this->expectExceptionMessage('Failed tearing down "' . $class . '::throwEachTestException" #[AfterEachTest] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite AfterEachTest".');

        $this->testSuiteRunner->runTestSuites($results);
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteBeforeEachTestHaltsTestProcessing() {
        $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteBeforeEachTest');
        $results = $this->parser->parse($dir);

        $this->expectException(TestSetUpException::class);
        $class = ExplicitTestSuite\ExceptionThrowingTestSuiteBeforeEachTest\MyTestSuite::class;
        $this->expectExceptionMessage('Failed setting up "' . $class . '::throwEachTestException" #[BeforeEachTest] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite BeforeEachTest".');

        $this->testSuiteRunner->runTestSuites($results);
    }

    public function testExplicitTestSuiteExceptionThrowingTestSuiteAfterAllHaltsTestProcessing() {
        $dir = $this->explicitTestSuitePath('ExceptionThrowingTestSuiteAfterAll');
        $results = $this->parser->parse($dir);

        $this->expectException(TestSuiteTearDownException::class);
        $class = ExplicitTestSuite\ExceptionThrowingTestSuiteAfterAll\MyTestSuite::class;
        $this->expectExceptionMessage('Failed tearing down "' . $class . '::throwException" #[AfterAll] hook with exception of type "RuntimeException" with code 0 and message "AttachToTestSuite AfterAll".');

        $this->testSuiteRunner->runTestSuites($results);
    }

}