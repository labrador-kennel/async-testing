<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Statistics;

use Labrador\AsyncUnit\Framework\Parser\ParserResult;

/**
 * @package Labrador\AsyncUnit\Framework\Statistics
 * @internal
 */
final class SummaryCalculator {

    private array $modelRelationships = [];
    private AggregateSummary $aggregateSummary;

    private int $testSuiteCount = 0;
    private int $disabledTestSuiteCount = 0;

    private int $testCaseCount = 0;
    private array $disabledTestCases = [];

    private int $testCount = 0;
    private array $disabledTests = [];

    public function __construct(private ParserResult $parserResult) {
        $this->calculateModelRelationships($this->parserResult);
    }

    private function calculateModelRelationships(ParserResult $parserResult) {
        foreach ($parserResult->getTestSuiteModels() as $testSuiteModel) {
            $testSuite = $testSuiteModel->getClass();
            $this->testSuiteCount++;
            if ($testSuiteModel->isDisabled()) {
                $this->disabledTestSuiteCount++;
            }
            if (!isset($this->modelRelationships[$testSuite])) {
                $this->modelRelationships[$testSuite] = [];
            }

            foreach ($testSuiteModel->getTestCaseModels() as $testCaseModel) {
                $testCase = $testCaseModel->getClass();
                $this->testCaseCount++;
                if ($testCaseModel->isDisabled()) {
                    $this->disabledTestCases[] = $testCaseModel->getClass();
                }
                if (!isset($this->modelRelationships[$testSuite][$testCase])) {
                    $this->modelRelationships[$testSuite][$testCase] = [];
                }

                foreach ($testCaseModel->getTestModels() as $testModel) {
                    $this->testCount++;
                    $testName = sprintf(
                        '%s::%s',
                        $testModel->getClass(),
                        $testModel->getMethod()
                    );
                    if ($testModel->isDisabled()) {
                        $this->disabledTests[] = $testName;
                    }
                    $this->modelRelationships[$testSuite][$testCase][] = $testName;
                }
            }

        }
    }

    public function getAggregateSummary() : AggregateSummary {
        if (!isset($this->aggregateSummary)) {
            $this->aggregateSummary = $this->constructAggregateSummary();
        }

        return $this->aggregateSummary;
    }

    private function constructAggregateSummary() : AggregateSummary {
        $testSuiteNames = array_keys($this->modelRelationships);
        return new class(
            $testSuiteNames,
            $this->testSuiteCount,
            $this->disabledTestSuiteCount,
            $this->testCaseCount,
            count($this->disabledTestCases),
            $this->testCount,
            count($this->disabledTests)
        ) implements AggregateSummary {

            public function __construct(
                private array $testSuiteNames,
                private int $totalTestSuiteCount,
                private int $disabledTestSuiteCount,
                private int $totalTestCaseCount,
                private int $disabledTestCaseCount,
                private int $totalTestCount,
                private int $disabledTestCount
            ) {}

            public function getTestSuiteNames() : array {
                return $this->testSuiteNames;
            }

            public function getTotalTestSuiteCount() : int {
                return $this->totalTestSuiteCount;
            }

            public function getDisabledTestSuiteCount() : int {
                return $this->disabledTestSuiteCount;
            }

            public function getTotalTestCaseCount() : int {
                return $this->totalTestCaseCount;
            }

            public function getDisabledTestCaseCount() : int {
                return $this->disabledTestCaseCount;
            }

            public function getTotalTestCount() : int {
                return $this->totalTestCount;
            }

            public function getDisabledTestCount() : int {
                return $this->disabledTestCount;
            }
        };
    }

    public function getTestSuiteSummary(string $testSuite) : TestSuiteSummary {
        $testCaseNames = array_keys($this->modelRelationships[$testSuite]);
        $testCaseCount = count($testCaseNames);
        $disabledTestCaseCount = count(array_intersect($this->disabledTestCases, $testCaseNames));
        $suiteTests = [];
        foreach ($this->modelRelationships[$testSuite] as $tests) {
            foreach ($tests as $test) {
                $suiteTests[] = $test;
            }
        }
        $disabledTestCount = count(array_intersect($this->disabledTests, $suiteTests));
        return new class(
            $testSuite,
            $testCaseNames,
            $testCaseCount,
            $disabledTestCaseCount,
            count($suiteTests),
            $disabledTestCount
        ) implements TestSuiteSummary {

            public function __construct(
                private string $testSuiteName,
                private array $testCaseNames,
                private int $testCaseCount,
                private int $disabledTestCaseCount,
                private int $testCount,
                private int $disabledTestCount
            ) {}

            public function getTestSuiteName() : string {
                return $this->testSuiteName;
            }

            public function getTestCaseNames() : array {
                return $this->testCaseNames;
            }

            public function getTestCaseCount() : int {
                return $this->testCaseCount;
            }

            public function getDisabledTestCaseCount() : int {
                return $this->disabledTestCaseCount;
            }

            public function getTestCount() : int {
                return $this->testCount;
            }

            public function getDisabledTestCount() : int {
                return $this->disabledTestCount;
            }
        };
    }

    public function getTestCaseSummary(string $testCase) : TestCaseSummary {
        $testSuite = null;
        $tests = null;
        foreach ($this->modelRelationships as $_testSuite => $testCases) {
            if (array_key_exists($testCase, $testCases)) {
                $testSuite = $_testSuite;
                $tests = $testCases[$testCase];
                break;
            }
        }
        $disabledTestCount = count(array_intersect($this->disabledTests, $tests));

        return new class(
            $testSuite,
            $testCase,
            $tests,
            count($tests),
            $disabledTestCount
        ) implements TestCaseSummary {

            public function __construct(
                private string $testSuiteName,
                private string $testCaseName,
                private array $testNames,
                private int $testCount,
                private int $disabledTestCount
            ) {}

            public function getTestSuiteName() : string {
                return $this->testSuiteName;
            }

            public function getTestCaseName() : string {
                return $this->testCaseName;
            }

            public function getTestNames() : array {
                return $this->testNames;
            }

            public function getTestCount() : int {
                return $this->testCount;
            }

            public function getDisabledTestCount() : int {
                return $this->disabledTestCount;
            }
        };
    }
}