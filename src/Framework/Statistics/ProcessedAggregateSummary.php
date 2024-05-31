<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Statistics;

use SebastianBergmann\Timer\Duration;

interface ProcessedAggregateSummary {

    public function getTestSuiteNames() : array;

    public function getTotalTestSuiteCount() : int;

    public function getDisabledTestSuiteCount() : int;

    public function getTotalTestCaseCount() : int;

    public function getDisabledTestCaseCount() : int;

    public function getTotalTestCount() : int;

    public function getDisabledTestCount() : int;

    public function getPassedTestCount() : int;

    public function getFailedTestCount() : int;

    public function getErroredTestCount() : int;

    public function getAssertionCount() : int;

    public function getDuration() : Duration;

    public function getMemoryUsageInBytes() : int;

}