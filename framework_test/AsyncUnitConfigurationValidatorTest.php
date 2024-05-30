<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\Loop;
use Cspray\Labrador\AsyncUnit\Configuration\AsyncUnitConfigurationValidator;
use Cspray\Labrador\AsyncUnit\Configuration\ConfigurationValidationResults;
use Cspray\Labrador\AsyncUnit\Configuration\ConfigurationValidator;
use Cspray\Labrador\AsyncUnit\Stub\TestConfiguration;
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

    public function testEmptyTestDirectoriesIsInvalid() {
        $this->testConfiguration->setTestDirectories([]);
        $results = $this->subject->validate($this->testConfiguration);

        $this->assertInstanceOf(ConfigurationValidationResults::class, $results);
        $this->assertFalse($results->isValid());
        $this->assertArrayHasKey('testDirectories', $results->getValidationErrors());
        $this->assertSame(
            ['Must provide at least one directory to scan but none were provided.'],
            $results->getValidationErrors()['testDirectories']
        );
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
            ['The result printer "Generator" is not a ' . ResultPrinterPlugin::class . '. Please ensure your result printer implements this interface.'],
            $results->getValidationErrors()['resultPrinter']
        );
    }

}