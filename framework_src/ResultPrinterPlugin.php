<?php declare(strict_types=1);

namespace Cspray\Labrador\AsyncUnit;

use Amp\ByteStream\WritableStream;
use Labrador\AsyncEvent\EventEmitter;

interface ResultPrinterPlugin {

    public function registerEvents(EventEmitter $emitter, WritableStream $output) : void;

}