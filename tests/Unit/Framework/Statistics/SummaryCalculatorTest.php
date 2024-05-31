<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Test\Unit\Framework\Statistics;

use Labrador\AsyncUnit\Framework\ImplicitTestSuite;
use Labrador\AsyncUnit\Framework\Parser\StaticAnalysisParser;
use Labrador\AsyncUnit\Framework\Statistics\SummaryCalculator;
use Labrador\AsyncUnit\Test\Unit\Framework\UsesAcmeSrc;
use Acme\DemoSuites\ImplicitDefaultTestSuite;
use Acme\DemoSuites\ExplicitTestSuite;
use PHPUnit\Framework\TestCase;

class SummaryCalculatorTest extends TestCase {

    use UsesAcmeSrc;

    public function aggregateSummaryTestSuiteNamesProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), [ImplicitTestSuite::class]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), [
                ImplicitTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class
            ]]
        ];
    }

    /**
     * @dataProvider aggregateSummaryTestSuiteNamesProvider
     */
    public function testGetAggregateSummaryGetTestSuiteNames(string $path, array $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertEqualsCanonicalizing($expected, $calculator->getAggregateSummary()->getTestSuiteNames());
    }

    public function aggregateSummaryTotalTestSuiteCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 3]
        ];
    }

    /**
     * @dataProvider aggregateSummaryTotalTestSuiteCountProvider
     */
    public function testGetAggregateSummaryGetTotalTestSuiteCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getTotalTestSuiteCount());
    }

    public function aggregateSummaryDisabledTestSuiteCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), 0],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 1]
        ];
    }

    /**
     * @dataProvider aggregateSummaryDisabledTestSuiteCountProvider
     */
    public function testGetAggregateSummaryGetDisabledTestSuiteCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getDisabledTestSuiteCount());
    }

    public function aggregateSummaryTotalTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), 3],
            [$this->implicitDefaultTestSuitePath('MultipleTestCase'), 3]
        ];
    }

    /**
     * @dataProvider aggregateSummaryTotalTestCaseCountProvider
     */
    public function testGetAggregateSummaryGetTestCaseCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getTotalTestCaseCount());
    }

    public function aggregateSummaryDisabledTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 1],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), 2]
        ];
    }

    /**
     * @dataProvider aggregateSummaryDisabledTestCaseCountProvider
     */
    public function testGetAggregateSummaryGetDisabledTestCaseCount(string $path, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $this->assertSame($expected, $calculator->getAggregateSummary()->getDisabledTestCaseCount());
    }

    public function aggregateSummaryTotalTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 1],
            [$this->implicitDefaultTestSuitePath('MultipleTest'), 3],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), 9]
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

    public function aggregateSummaryDisabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), 0],
            [$this->implicitDefaultTestSuitePath('TestDisabled'), 1],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), 3]
        ];
    }

    /**
     * @dataProvider aggregateSummaryDisabledTestCountProvider
     */
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

    public function suiteSummaryTestCaseNamesProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, [ImplicitDefaultTestSuite\SingleTest\MyTestCase::class]],
            [$this->implicitDefaultTestSuitePath('MultipleTest'), ImplicitTestSuite::class, [ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class,
                ImplicitDefaultTestSuite\KitchenSink\SecondTestCase::class
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, [
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class,
                ImplicitDefaultTestSuite\KitchenSink\WhatAbout\SamwiseTestCase::class
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, [
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\FoodAndBeverageTestCase::class,
                ImplicitDefaultTestSuite\KitchenSink\SecondBreakfast\BadTestCase::class
            ]]
        ];
    }

    /**
     * @dataProvider suiteSummaryTestCaseNamesProvider
     */
    public function testGetTestSuiteSummaryGetTestCaseNames(string $path, string $testSuite, array $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertEqualsCanonicalizing($expected, $testSuiteSummary->getTestCaseNames());
    }

    public function suiteSummaryTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, 1],
            [$this->implicitDefaultTestSuitePath('MultipleTest'), ImplicitTestSuite::class, 1],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, 2],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, 3],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, 2]
        ];
    }

    /**
     * @dataProvider suiteSummaryTestCaseCountProvider
     */
    public function testGetTestSuiteSummaryGetTestCaseCount(string $path, string $testSuite, int $expected) {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertSame($expected, $testSuiteSummary->getTestCaseCount());
    }

    public function suiteSummaryDisabledTestCaseCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), ImplicitTestSuite::class, 1],
            [$this->explicitTestSuitePath('TestSuiteDisabled'), ExplicitTestSuite\TestSuiteDisabled\MyTestSuite::class, 2],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, 0]
        ];
    }

    /**
     * @dataProvider suiteSummaryDisabledTestCaseCountProvider
     */
    public function testGetTestSuiteSummaryGetDisabledTestCaseCount(string $path, string $testSuite, int $expected) {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertSame($expected, $testSuiteSummary->getDisabledTestCaseCount());
    }

    public function suiteSummaryTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, 1],
            [$this->implicitDefaultTestSuitePath('MultipleTestCase'), ImplicitTestSuite::class, 4],
            [$this->implicitDefaultTestSuitePath('ExtendedTestCases'), ImplicitTestSuite::class, 9],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, 5],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, 3],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, 2]
        ];
    }

    /**
     * @dataProvider suiteSummaryTestCountProvider
     */
    public function testGetTestSuiteSummaryGetTestCount(string $path, string $testSuite, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertSame($expected, $testSuiteSummary->getTestCount());
    }

    public function suiteSummaryDisabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), ImplicitTestSuite::class, 0],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), ImplicitTestSuite::class, 3],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitTestSuite::class, 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestSuite::class, 2],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class, 1]
        ];
    }

    /**
     * @dataProvider suiteSummaryDisabledTestCountProvider
     */
    public function testGetTestSuiteSummaryGetDisabledTestCount(string $path, string $testSuite, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestSuiteSummary($testSuite);
        $this->assertSame($expected, $testSuiteSummary->getDisabledTestCount());
    }

    public function caseSummaryTestSuiteNameProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, ImplicitTestSuite::class],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\FrodoTestCase::class, ImplicitDefaultTestSuite\KitchenSink\WhatAbout\PotatoTestSuite::class]
        ];
    }

    /**
     * @dataProvider caseSummaryTestSuiteNameProvider
     */
    public function testGetTestCaseSummaryGetTestSuiteName(string $path, string $testCase, string $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testSuiteSummary = $calculator->getTestCaseSummary($testCase);
        $this->assertSame($expected, $testSuiteSummary->getTestSuiteName());
    }

    /**
     * @dataProvider caseSummaryTestSuiteNameProvider
     */
    public function testGetTestCaseSummaryGetTestCaseName(string $path, string $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testCaseSummary = $calculator->getTestCaseSummary($expected);
        $this->assertSame($expected, $testCaseSummary->getTestCaseName());
    }

    public function caseSummaryTestNamesProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, [
                ImplicitDefaultTestSuite\SingleTest\MyTestCase::class . '::ensureSomethingHappens'
            ]],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class, [
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::testOne',
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::testTwo',
                ImplicitDefaultTestSuite\KitchenSink\FirstTestCase::class . '::disabledTest',
            ]]
        ];
    }

    /**
     * @dataProvider caseSummaryTestNamesProvider
     */
    public function testGetTestCaseSummaryGetTestNames(string $path, string $testCase, array $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testCaseSummary = $calculator->getTestCaseSummary($testCase);
        $this->assertEqualsCanonicalizing($expected, $testCaseSummary->getTestNames());
    }

    public function caseSummaryTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, 1],
            [$this->implicitDefaultTestSuitePath('MultipleTestCase'), ImplicitDefaultTestSuite\MultipleTestCase\FooTestCase::class, 2],
            [$this->implicitDefaultTestSuitePath('MultipleTest'), ImplicitDefaultTestSuite\MultipleTest\MyTestCase::class, 3],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class, 1]
        ];
    }

    /**
     * @dataProvider caseSummaryTestCountProvider
     */
    public function testGetTestCaseSummaryGetTestCount(string $path, string $testCase, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testCaseSummary = $calculator->getTestCaseSummary($testCase);
        $this->assertSame($expected, $testCaseSummary->getTestCount());
    }

    public function caseSummaryDisabledTestCountProvider() : array {
        return [
            [$this->implicitDefaultTestSuitePath('SingleTest'), ImplicitDefaultTestSuite\SingleTest\MyTestCase::class, 0],
            [$this->implicitDefaultTestSuitePath('KitchenSink'), ImplicitDefaultTestSuite\KitchenSink\WhatAbout\BilboTestCase::class, 1],
            [$this->implicitDefaultTestSuitePath('TestCaseDisabled'), ImplicitDefaultTestSuite\TestCaseDisabled\MyTestCase::class, 3]
        ];
    }

    /**
     * @dataProvider caseSummaryDisabledTestCountProvider
     */
    public function testGetTestCaseSummaryGetDisabledTestCount(string $path, string $testCase, int $expected) : void {
        $results = (new StaticAnalysisParser())->parse($path);
        $calculator = new SummaryCalculator($results);

        $testCaseSummary = $calculator->getTestCaseSummary($testCase);
        $this->assertSame($expected, $testCaseSummary->getDisabledTestCount());
    }
}