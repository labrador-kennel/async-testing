<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit\Configuration;

interface Configuration {

    /**
     * @return string[]
     */
    public function getTestDirectories() : array;

    /**
     * @return string[]
     */
    public function getPlugins() : array;

    /**
     * @return string
     */
    public function getResultPrinter() : string;

    /**
     * @return string|null
     */
    public function getMockBridge() : ?string;

}