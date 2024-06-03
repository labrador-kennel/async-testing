<?php declare(strict_types=1);


namespace Labrador\AsyncUnit\Framework\Statistics;


use SebastianBergmann\Timer\Duration;

interface ProcessedTestCaseSummary {

    public function getTestSuiteName() : string;

    public function getTestCaseName() : string;

    public function getTestNames() : array;

    public function getTestCount() : int;

    public function getDisabledTestCount() : int;

    public function getPassedTestCount() : int;

    public function getFailedTestCount() : int;

    public function getErroredTestCount() : int;

    public function getAssertionCount() : int;

    public function getDuration() : Duration;

}