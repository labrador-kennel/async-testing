<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework\Plugin;

use Amp\ByteStream\WritableStream;
use Labrador\AsyncEvent\Emitter;

interface ResultPrinterPlugin {

    public function registerEvents(Emitter $emitter, WritableStream $output) : void;

}