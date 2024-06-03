<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Statistics;

interface TestCaseSummary {

    public function getTestSuiteName() : string;

    public function getTestCaseName() : string;

    public function getTestNames() : array;

    public function getTestCount() : int;

    public function getDisabledTestCount() : int;
}