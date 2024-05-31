<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Model;

/**
 * Represents a Plugin that the library treats as a first-class citizen and will autoregister upon Application
 * startup.
 *
 * @package Labrador\AsyncUnit\Framework\Model
 */
final class PluginModel {

    public function __construct(private string $pluginClass) {}

    public function getPluginClass() : string {
        return $this->pluginClass;
    }

}