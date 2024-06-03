<?php declare(strict_types=1);

namespace Labrador\AsyncUnit\Framework;

use Amp\ByteStream\WritableStream;
use Labrador\AsyncEvent\Emitter;

interface ResultPrinter {

    public function registerEvents(Emitter $emitter, WritableStream $writableStream) : void;

}