<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Statistics;

interface TestSuiteSummary {

    public function getTestSuiteName() : string;

    public function getTestCaseNames() : array;

    public function getTestCaseCount() : int;

    public function getDisabledTestCaseCount() : int;

    public function getTestCount() : int;

    public function getDisabledTestCount() : int;

}