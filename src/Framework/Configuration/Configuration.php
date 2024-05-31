<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Configuration;

use Labrador\AsyncUnit\Framework\MockBridge\MockBridge;
use Labrador\AsyncUnit\Framework\ResultPrinter;

interface Configuration {

    /**
     * @return non-empty-list<non-empty-string>
     */
    public function getTestDirectories() : array;

    /**
     * @return class-string<ResultPrinter>
     */
    public function getResultPrinter() : string;

    /**
     * @return class-string<MockBridge>|null
     */
    public function getMockBridge() : ?string;

}