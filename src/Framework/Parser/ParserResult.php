<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Parser;

use Labrador\AsyncUnit\Framework\Model\PluginModel;
use Labrador\AsyncUnit\Framework\Model\TestSuiteModel;
use Labrador\AsyncUnit\Framework\Statistics\AggregateSummary;
use Labrador\AsyncUnit\Framework\Statistics\SummaryCalculator;
use Labrador\AsyncUnit\Framework\Statistics\TestCaseSummary;
use Labrador\AsyncUnit\Framework\Statistics\TestSuiteSummary;

final class ParserResult {

    private SummaryCalculator $summaryCalculator;

    public function __construct(private AsyncUnitModelCollector $collector) {}

    /**
     * @return list<TestSuiteModel>
     */
    public function getTestSuiteModels() : array {
        return $this->collector->getTestSuiteModels();
    }

    /**
     * @return PluginModel[]
     */
    public function getPluginModels() : array {
        return $this->collector->getPluginModels();
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
        if (!isset($this->summaryCalculator)) {
            $this->summaryCalculator = new SummaryCalculator($this);
        }
        return $this->summaryCalculator;
    }

}