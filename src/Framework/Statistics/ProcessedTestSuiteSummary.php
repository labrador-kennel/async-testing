<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Statistics;

use SebastianBergmann\Timer\Duration;

interface ProcessedTestSuiteSummary {

    public function getTestSuiteName() : string;

    public function getTestCaseNames() : array;

    public function getTestCaseCount() : int;

    public function getDisabledTestCaseCount() : int;

    public function getTestCount() : int;

    public function getDisabledTestCount() : int;

    public function getPassedTestCount() : int;

    public function getFailedTestCount() : int;

    public function getErroredTestCount() : int;

    public function getAssertionCount() : int;

    public function getDuration() : Duration;

}