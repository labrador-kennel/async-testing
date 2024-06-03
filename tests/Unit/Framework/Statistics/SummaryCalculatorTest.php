<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Statistics;

use Labrador\AsyncUnit\Framework\ImplicitTestSuite;
use Labrador\AsyncUnit\Framework\Parser\StaticAnalysisParser;
use Labrador\AsyncUnit\Framework\Statistics\SummaryCalculator;
use Labrador\AsyncUnit\Test\Unit\Framework\UsesAcmeSrc;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SummaryCalculatorTest extends TestCase {

    use UsesAcmeSrc;

    public static function aggregateSummaryTestSuiteNamesProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class]],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class
            ]]
        ];
    }

    #[DataProvider('aggregateSummaryTestSuiteNamesProvider')]
    public function testGetAggregateSummaryGetTestSuiteNames(string $path, array $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertEqualsCanonicalizing($expected, $calculator->getAggregateSummary()->getTestSuiteNames());
    }

    public static function aggregateSummaryTotalTestSuiteCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 1],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 3]
        ];
    }

    #[DataProvider('aggregateSummaryTotalTestSuiteCountProvider')]
    public function testGetAggregateSummaryGetTotalTestSuiteCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getTotalTestSuiteCount());
    }

    public static function aggregateSummaryDisabledTestSuiteCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 0],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), 0],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 1]
        ];
    }

    #[DataProvider('aggregateSummaryDisabledTestSuiteCountProvider')]
    public function testGetAggregateSummaryGetDisabledTestSuiteCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getDisabledTestSuiteCount());
    }

    public static function aggregateSummaryTotalTestCaseCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 1],
            'ExtendedTestCases' => [self::implicitDefaultTestSuitePath('ExtendedTestCases'), 3],
            'MultipleTestCase' => [self::implicitDefaultTestSuitePath('MultipleTestCase'), 3]
        ];
    }

    #[DataProvider('aggregateSummaryTotalTestCaseCountProvider')]
    public function testGetAggregateSummaryGetTestCaseCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getTotalTestCaseCount());
    }

    public static function aggregateSummaryDisabledTestCaseCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 0],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), 1],
            'TestSuiteDisabled' => [self::explicitTestSuitePath('TestSuiteDisabled'), 2]
        ];
    }

    #[DataProvider('aggregateSummaryDisabledTestCaseCountProvider')]
    public function testGetAggregateSummaryGetDisabledTestCaseCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getDisabledTestCaseCount());
    }

    public static function aggregateSummaryTotalTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), 1],
            'MultipleTest' => [self::implicitDefaultTestSuitePath('MultipleTest'), 3],
            'ExtendedTestCases' => [self::implicitDefaultTestSuitePath('ExtendedTestCases'), 9]
        ];
    }

    /**
     * @dataProvider aggregateSummaryTotalTestCountProvider
     */
    public function testGetAggregateSummaryGetTestCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getTotalTestCount());
    }

    public static function aggregateSummaryDisabledTestCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), 0],
            [self::implicitDefaultTestSuitePath('TestDisabled'), 1],
            [self::implicitDefaultTestSuitePath('TestCaseDisabled'), 3]
        ];
    }

    #[DataProvider('aggregateSummaryDisabledTestCountProvider')]
    public function testGetAggregateSummaryGetDisabledTestCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getDisabledTestCount());
    }

    public function testGetAggregateSummarySameObject() : void {
        $results = (new StaticAnalysisParser())->parse($this->implicitDefaultTestSuitePath('SingleTest'));
        $calculator = new SummaryCalculator($results);

        $this->assertSame($calculator->getAggregateSummary(), $calculator->getAggregateSummary());
    }

    public function testGetTestSuiteSummaryGetTestSuiteName() {
        $results = (new StaticAnalysisParser())->parse($this->implicitDefaultTestSuitePath('SingleTest'));
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary(ImplicitTestSuite::class);
        $this->assertSame(ImplicitTestSuite::class, $testSuiteSummary->getTestSuiteName());
    }

    public static function suiteSummaryTestCaseNamesProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, [ImplicitDefaultTestSuite\SingleTest\MyTestCase::class]],
            [self::implicitDefaultTestSuitePath('MultipleTest'), ImplicitTestSuite::class, [ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class]],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class
            ]],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, [
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class
            ]],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, [
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class
            ]]
        ];
    }

    #[DataProvider('suiteSummaryTestCaseNamesProvider')]
    public function testGetTestSuiteSummaryGetTestCaseNames(string $path, string $testSuite, array $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertEqualsCanonicalizing($expected, $testSuiteSummary->getTestCaseNames());
    }

    public static function suiteSummaryTestCaseCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, 1],
            [self::implicitDefaultTestSuitePath('MultipleTest'), ImplicitTestSuite::class, 1],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, 2],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, 3],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, 2]
        ];
    }

    #[DataProvider('suiteSummaryTestCaseCountProvider')]
    public function testGetTestSuiteSummaryGetTestCaseCount(string $path, string $testSuite, int $expected) {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertSame($expected, $testSuiteSummary->getTestCaseCount());
    }

    public static function suiteSummaryDisabledTestCaseCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, 0],
            [self::implicitDefaultTestSuitePath('TestCaseDisabled'), ImplicitTestSuite::class, 1],
            [self::explicitTestSuitePath('TestSuiteDisabled'), ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class, 2],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, 0],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, 0],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, 0]
        ];
    }

    #[DataProvider('suiteSummaryDisabledTestCaseCountProvider')]
    public function testGetTestSuiteSummaryGetDisabledTestCaseCount(string $path, string $testSuite, int $expected) {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertSame($expected, $testSuiteSummary->getDisabledTestCaseCount());
    }

    public static function suiteSummaryTestCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, 1],
            [self::implicitDefaultTestSuitePath('MultipleTestCase'), ImplicitTestSuite::class, 4],
            [self::implicitDefaultTestSuitePath('ExtendedTestCases'), ImplicitTestSuite::class, 9],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, 5],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, 3],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, 2]
        ];
    }

    #[DataProvider('suiteSummaryTestCountProvider')]
    public function testGetTestSuiteSummaryGetTestCount(string $path, string $testSuite, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertSame($expected, $testSuiteSummary->getTestCount());
    }

    public static function suiteSummaryDisabledTestCountProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, 0],
            [self::implicitDefaultTestSuitePath('TestCaseDisabled'), ImplicitTestSuite::class, 3],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, 0],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, 2],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, 1]
        ];
    }

    #[DataProvider('suiteSummaryDisabledTestCountProvider')]
    public function testGetTestSuiteSummaryGetDisabledTestCount(string $path, string $testSuite, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertSame($expected, $testSuiteSummary->getDisabledTestCount());
    }

    public static function caseSummaryTestSuiteNameProvider() : array {
        return [
            [self::implicitDefaultTestSuitePath('SingleTest'), ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, ImplicitTestSuite::class],
            [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class, ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class]
        ];
    }

    #[DataProvider('caseSummaryTestSuiteNameProvider')]
    public function testGetTestCaseSummaryGetTestSuiteName(string $path, string $testCase, string $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestCaseSummary($testCase);
        $this->assertSame($expected, $testSuiteSummary->getTestSuiteName());
    }

    #[DataProvider('caseSummaryTestSuiteNameProvider')]
    public function testGetTestCaseSummaryGetTestCaseName(string $path, string $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testCaseSummary = $calculator->getTestCaseSummary($expected);
        $this->assertSame($expected, $testCaseSummary->getTestCaseName());
    }

    public static function caseSummaryTestNamesProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class . '::ensureSomethingHappens'
            ]],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class, [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::testOne',
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::testTwo',
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::disabledTest',
            ]]
        ];
    }

    #[DataProvider('caseSummaryTestNamesProvider')]
    public function testGetTestCaseSummaryGetTestNames(string $path, string $testCase, array $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testCaseSummary = $calculator->getTestCaseSummary($testCase);
        $this->assertEqualsCanonicalizing($expected, $testCaseSummary->getTestNames());
    }

    public static function caseSummaryTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, 1],
            'MultipleTestCase' => [self::implicitDefaultTestSuitePath('MultipleTestCase'), ImplicitDefaultTestSuite\MultipleTestCase\FooTestCase::class, 2],
            'MultipleTest' => [self::implicitDefaultTestSuitePath('MultipleTest'), ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class, 3],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class, 1]
        ];
    }

    #[DataProvider('caseSummaryTestCountProvider')]
    public function testGetTestCaseSummaryGetTestCount(string $path, string $testCase, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testCaseSummary = $calculator->getTestCaseSummary($testCase);
        $this->assertSame($expected, $testCaseSummary->getTestCount());
    }

    public static function caseSummaryDisabledTestCountProvider() : array {
        return [
            'SingleTest' => [self::implicitDefaultTestSuitePath('SingleTest'), ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, 0],
            'KitchenSink' => [self::implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class, 1],
            'TestCaseDisabled' => [self::implicitDefaultTestSuitePath('TestCaseDisabled'), ImplicitDefaultTestSuite\TestCaseDisabled\MyTestCase::class, 3]
        ];
    }

    #[DataProvider('caseSummaryDisabledTestCountProvider')]
    public function testGetTestCaseSummaryGetDisabledTestCount(string $path, string $testCase, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testCaseSummary = $calculator->getTestCaseSummary($testCase);
        $this->assertSame($expected, $testCaseSummary->getDisabledTestCount());
    }
}