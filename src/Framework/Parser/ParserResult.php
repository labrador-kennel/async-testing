<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Parser;

use Labrador\AsyncUnit\Framework\Model\TestSuiteModel;
use Labrador\AsyncUnit\Framework\Statistics\AggregateSummary;
use Labrador\AsyncUnit\Framework\Statistics\SummaryCalculator;
use Labrador\AsyncUnit\Framework\Statistics\TestCaseSummary;
use Labrador\AsyncUnit\Framework\Statistics\TestSuiteSummary;

final class ParserResult {

    private readonly SummaryCalculator $summaryCalculator;

    public function __construct(private AsyncUnitModelCollector $collector) {
        $this->summaryCalculator = new SummaryCalculator($this);
    }

    /**
     * @return list<TestSuiteModel>
     */
    public function getTestSuiteModels() : array {
        return $this->collector->getTestSuiteModels();
    }

    public function getAggregateSummary() : AggregateSummary {
        return $this->getSummaryCalculator()->getAggregateSummary();
    }

    public function getTestSuiteSummary(string $testSuite) : TestSuiteSummary {
        return $this->getSummaryCalculator()->getTestSuiteSummary($testSuite);
    }

    public function getTestCaseSummary(string $testCase) : TestCaseSummary {
        return $this->getSummaryCalculator()->getTestCaseSummary($testCase);
    }

    private function getSummaryCalculator() : SummaryCalculator {
        return $this->summaryCalculator;
    }

}