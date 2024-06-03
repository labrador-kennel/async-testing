<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework;

use Labrador\AsyncUnit\Framework\Configuration\AsyncUnitConfigurationValidator;
use Labrador\AsyncUnit\Framework\Configuration\ConfigurationValidationResults;
use Labrador\AsyncUnit\Framework\Configuration\ConfigurationValidator;
use Labrador\AsyncUnit\Framework\ResultPrinter;
use Labrador\AsyncUnit\Test\Unit\Framework\Stub\TestConfiguration;
use Generator;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

class AsyncUnitConfigurationValidatorTest extends PHPUnitTestCase {

    private ConfigurationValidator $subject;

    private TestConfiguration $testConfiguration;

    public function setUp(): void {
        parent::setUp();
        $this->subject = new AsyncUnitConfigurationValidator();
        $this->testConfiguration = new TestConfiguration();
    }

    public function testNonDirectoriesIsInvalid() {
        $this->testConfiguration->setTestDirectories([
            __DIR__,
            'not a dir',
            dirname(__DIR__),
            'definitely not a dir'
        ]);
        $results = $this->subject->validate($this->testConfiguration);

        $this->assertInstanceOf(ConfigurationValidationResults::class, $results);
        $this->assertFalse($results->isValid());
        $this->assertArrayHasKey('testDirectories', $results->getValidationErrors());
        $this->assertSame(
            [
                'A configured directory to scan, "not a dir", is not a directory.',
                'A configured directory to scan, "definitely not a dir", is not a directory.'
            ],
            $results->getValidationErrors()['testDirectories']
        );
    }

    public function testResultPrinterClassIsNotClass() {
        $this->testConfiguration->setResultPrinterClass('Not a class');
        $results = $this->subject->validate($this->testConfiguration);

        $this->assertInstanceOf(ConfigurationValidationResults::class, $results);
        $this->assertFalse($results->isValid());
        $this->assertArrayHasKey('resultPrinter', $results->getValidationErrors());
        $this->assertSame(
            ['The result printer "Not a class" is not a class that can be found. Please ensure this class is configured to be autoloaded through Composer.'],
            $results->getValidationErrors()['resultPrinter']
        );
    }

    public function testResultPrinterClassIsNotResultPrinterPlugin() {
        $this->testConfiguration->setResultPrinterClass(Generator::class);
        $results = $this->subject->validate($this->testConfiguration);

        $this->assertInstanceOf(ConfigurationValidationResults::class, $results);
        $this->assertFalse($results->isValid());
        $this->assertArrayHasKey('resultPrinter', $results->getValidationErrors());
        $this->assertSame(
            ['The result printer "Generator" is not a ' . ResultPrinter::class . '. Please ensure your result printer implements this interface.'],
            $results->getValidationErrors()['resultPrinter']
        );
    }

}