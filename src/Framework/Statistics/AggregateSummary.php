<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Statistics;

/**
 */
interface AggregateSummary {

    public function getTestSuiteNames() : array;

    public function getTotalTestSuiteCount() : int;

    public function getDisabledTestSuiteCount() : int;

    public function getTotalTestCaseCount() : int;

    public function getDisabledTestCaseCount() : int;

    public function getTotalTestCount() : int;

    public function getDisabledTestCount() : int;

}